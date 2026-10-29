<?php

// Reconcile and release receipts held in "Cycle review" on Late Letters
// Dry run (default): php database/manual/029_release_held_late_letters.php
// Apply: php database/manual/029_release_held_late_letters.php --apply
// Optional filters: --branch=001 --receipt=1120
//
// Never run Laravel database migrations in this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

ini_set('memory_limit', '512M');

use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptPenaltySchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$options = [];
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--') && str_contains($arg, '=')) {
        [$k, $v] = explode('=', substr($arg, 2), 2);
        $options[$k] = $v;
    }
}

$branchFilter = $options['branch'] ?? null;
$receiptFilter = $options['receipt'] ?? null;

$schedule = new ReceiptPenaltySchedule();

// 1. Identify latest cycle dates per receipt
$rankedCycles = DB::table('t_pawn_trans')
    ->select('BC', 'code', 'dDate')
    ->selectRaw('ROW_NUMBER() OVER (PARTITION BY BC, code ORDER BY dDate DESC, id DESC) AS cycle_rank')
    ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING']);
$latestCycles = DB::query()->fromSub($rankedCycles, 'ranked_letter_cycles')
    ->select('BC', 'code', 'dDate')->where('cycle_rank', 1);

$query = TPawnSum::query()
    ->select('t_pawn_sums.*', 'latest_letter_cycle.dDate AS latest_letter_cycle_date')
    ->leftJoinSub($latestCycles, 'latest_letter_cycle', function ($join) {
        $join->whereRaw('latest_letter_cycle.BC = t_pawn_sums.BC COLLATE utf8mb4_general_ci')
            ->whereRaw('latest_letter_cycle.code = CONVERT(t_pawn_sums.Receipt_Number USING utf8mb4) COLLATE utf8mb4_general_ci');
    })
    ->where('t_pawn_sums.IsRedeemed', 0)
    ->where('t_pawn_sums.isForfeit', 0)
    ->where(function ($q) {
        $q->where('t_pawn_sums.is_letter_1', 1)
            ->orWhere('t_pawn_sums.is_letter_2', 1)
            ->orWhere('t_pawn_sums.is_letter_3', 1);
    });

if ($branchFilter) {
    $query->where('t_pawn_sums.BC', $branchFilter);
}
if ($receiptFilter) {
    $query->where('t_pawn_sums.Receipt_Number', $receiptFilter);
}

$candidates = [];
$today = today();

foreach ($query->get() as $receipt) {
    if (!$receipt->is_letter_1 && !$receipt->is_letter_2 && !$receipt->is_letter_3) {
        continue;
    }

    $latestCycleDate = $receipt->latest_letter_cycle_date;
    $cycleStart = collect([$latestCycleDate, $receipt->RePawning_date, $receipt->Pawn_Date, $receipt->Receipt_Date])
        ->filter()->map(fn ($date) => Carbon::parse($date)->startOfDay())->sort()->last();
    if (!$cycleStart) {
        continue;
    }

    $hasPrior = false;
    $snapshots = [];
    foreach ([1, 2, 3] as $num) {
        $printedAt = $receipt->{'letter_'.$num.'_date'};
        if ($receipt->{'is_letter_'.$num} && $printedAt) {
            $parsedDate = Carbon::parse($printedAt)->startOfDay();
            if ($parsedDate->lt($cycleStart)) {
                $hasPrior = true;
            }
            $snapshots[] = [
                'letter_no' => $num,
                'printed_at' => $parsedDate->toDateString(),
                'postage_charge' => $receipt->{'letter_pay_'.['one', 'two', 'three'][$num - 1]} ?? null,
            ];
        }
    }

    if (!$hasPrior) {
        continue;
    }

    $expiry = $schedule->expiryDate($receipt);
    $candidates[] = [
        'id' => $receipt->id,
        'BC' => $receipt->BC,
        'receipt_number' => $receipt->Receipt_Number,
        'customer' => $receipt->Customer_Name,
        'amount' => $receipt->Amount,
        'cycle_start' => $cycleStart->toDateString(),
        'expiry_date' => $expiry->toDateString(),
        'is_expired' => $expiry->lte($today),
        'snapshots' => $snapshots,
    ];
}

$summary = [
    'mode' => $apply ? 'APPLY' : 'DRY-RUN',
    'database' => DB::getDatabaseName(),
    'branch_filter' => $branchFilter ?: 'ALL',
    'receipt_filter' => $receiptFilter ?: 'ALL',
    'total_candidates' => count($candidates),
    'expired_count' => count(array_filter($candidates, fn ($c) => $c['is_expired'])),
    'future_count' => count(array_filter($candidates, fn ($c) => !$c['is_expired'])),
];

echo json_encode($summary, JSON_PRETTY_PRINT).PHP_EOL;

if (!$candidates) {
    echo "No receipts require release.\n";
    exit(0);
}

if (!$apply) {
    echo "\nSample candidate receipts (first 10):\n";
    foreach (array_slice($candidates, 0, 10) as $c) {
        echo "  [BC {$c['BC']}] Receipt #{$c['receipt_number']} ({$c['customer']}) - Exp: {$c['expiry_date']} (Expired? "
            .($c['is_expired'] ? 'YES' : 'NO').") - Snapshots: ".count($c['snapshots']).PHP_EOL;
    }
    echo "\nDry run complete. To execute changes, run with --apply\n";
    exit(0);
}

// Apply release
$lifecycle = app(ReceiptLifecycleService::class);
$applied = 0;
$failed = [];

foreach ($candidates as $cand) {
    try {
        DB::transaction(function () use ($cand, $lifecycle) {
            $receipt = TPawnSum::where('id', $cand['id'])->lockForUpdate()->firstOrFail();

            // 1. Record archive event with snapshots
            $lifecycle->record(
                $receipt,
                'PART_PAYMENT_ALLOCATION',
                0,
                'Prior cycle printed-letter history archived; receipt released for current loan cycle.',
                [
                    'cleared_letter_snapshots' => $cand['snapshots'],
                    'repair_source' => '029_release_held_late_letters',
                    'released_at' => now()->toIso8601String(),
                ]
            );

            // 2. Reactivate receipt to clear prior letter flags
            $lifecycle->reactivate(
                $receipt,
                0,
                'Prior cycle letters cleared; receipt released for current loan cycle.'
            );
        });
        $applied++;
    } catch (\Throwable $e) {
        $failed[] = [
            'id' => $cand['id'],
            'receipt' => $cand['receipt_number'],
            'error' => $e->getMessage(),
        ];
    }
}

echo json_encode([
    'status' => 'COMPLETED',
    'applied' => $applied,
    'failed_count' => count($failed),
    'failed' => $failed,
], JSON_PRETTY_PRINT).PHP_EOL;
