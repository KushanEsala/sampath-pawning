<?php

// Run manually: php database/manual/019_repair_zero_letter_postage.php [--apply]
// Dry-run by default. No Laravel migration or schema change is involved.

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ArrearsLetterEvent;
use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptTypeResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$resolver = app(ReceiptTypeResolver::class);
$lifecycle = app(ReceiptLifecycleService::class);
$plans = [];
$skipped = [];

foreach (ArrearsLetterEvent::where('postage_charge', 0)->orderBy('id')->cursor() as $event) {
    $receipt = TPawnSum::find($event->pawn_sum_id);
    $reason = null;
    $amount = 0.0;
    $source = null;
    $version = null;
    $field = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$event->letter_no - 1] ?? null;

    if (!$receipt || !$field || $receipt->BC !== $event->BC) {
        $reason = 'Missing/mismatched receipt or invalid letter number';
    } elseif ($receipt->IsRedeemed || $receipt->isForfeit) {
        $reason = 'Receipt is closed; historical payment needs individual review';
    } elseif ($receipt->Pawn_Date && Carbon::parse($receipt->Pawn_Date)->gt(Carbon::parse($event->issued_at))) {
        $reason = 'Receipt has a later pawn/payment cycle';
    } elseif (!(bool) $receipt->{'is_letter_'.$event->letter_no}) {
        $reason = 'Letter is not marked issued in the current receipt cycle';
    } elseif ((int) $event->cycle_no !== $lifecycle->cycleNumber($receipt)) {
        $reason = 'Letter event belongs to a previous arrears cycle';
    } else {
        $savedLetterCharge = (float) ($receipt->{$field} ?? 0);
        $savedReceiptCharge = (float) ($receipt->Postage_charge ?? 0);
        if ($savedLetterCharge > 0) {
            $amount = $savedLetterCharge;
            $source = 'saved letter charge';
        } elseif ($savedReceiptCharge > 0) {
            $amount = $savedReceiptCharge;
            $source = 'saved receipt postage snapshot';
        } else {
            $name = (string) ($receipt->receiptname ?: $receipt->Receipt_Type);
            $date = $receipt->Pawn_Date ?: $receipt->Receipt_Date ?: $receipt->Date;
            if ($name !== '' && $date) {
                $version = $resolver->resolveByDate($name, $date);
                $amount = (float) ($version->Postage_charge ?? 0);
                $source = 'historical receipt-type version';
            }
        }
        if ($amount <= 0) {
            $reason = 'No positive saved or historical postage amount';
        }
    }

    if ($reason) {
        $skipped[] = ['event_id' => $event->id, 'pawn_sum_id' => $event->pawn_sum_id, 'reason' => $reason];
        continue;
    }

    $plans[] = [
        'event_id' => $event->id,
        'pawn_sum_id' => $receipt->id,
        'branch' => $receipt->BC,
        'receipt_number' => $receipt->Receipt_Number,
        'letter_no' => $event->letter_no,
        'issued_at' => $event->issued_at->toDateTimeString(),
        'event_postage_before' => (float) $event->postage_charge,
        'receipt_letter_postage_before' => (float) ($receipt->{$field} ?? 0),
        'receipt_postage_snapshot' => (float) ($receipt->Postage_charge ?? 0),
        'new_postage' => round($amount, 2),
        'source' => $source,
        'type_version_id' => $version?->id,
        'type_version_effective_from' => $version?->effective_from?->toDateString(),
    ];
}

echo json_encode([
    'mode' => $apply ? 'apply' : 'dry-run',
    'planned_events' => count($plans),
    'skipped_events' => count($skipped),
    'receipt_2241' => array_values(array_filter($plans, fn ($row) => (int) $row['receipt_number'] === 2241)),
    'skipped' => $skipped,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (!$apply || !$plans) {
    exit(0);
}

$backupPath = storage_path('app/letter_postage_backup_'.date('Ymd_His').'.json');
if (file_put_contents($backupPath, json_encode(['plans' => $plans, 'skipped' => $skipped], JSON_PRETTY_PRINT)) === false) {
    throw new RuntimeException('Could not write backup; no database changes were made.');
}
@chmod($backupPath, 0600);
echo 'Backup: '.$backupPath.PHP_EOL;

$updated = DB::transaction(function () use ($plans, $lifecycle) {
    $count = 0;
    foreach ($plans as $plan) {
        $receipt = TPawnSum::whereKey($plan['pawn_sum_id'])->lockForUpdate()->first();
        $event = ArrearsLetterEvent::whereKey($plan['event_id'])->lockForUpdate()->first();
        $field = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$plan['letter_no'] - 1];
        if (!$receipt || !$event || $receipt->IsRedeemed || $receipt->isForfeit
            || (float) $event->postage_charge !== 0.0
            || !(bool) $receipt->{'is_letter_'.$plan['letter_no']}
            || (int) $event->cycle_no !== $lifecycle->cycleNumber($receipt)
            || ($receipt->Pawn_Date && Carbon::parse($receipt->Pawn_Date)->gt(Carbon::parse($event->issued_at)))) {
            throw new RuntimeException('Receipt/event changed during repair; all updates rolled back (event '.$plan['event_id'].').');
        }
        $event->postage_charge = $plan['new_postage'];
        $event->save();
        if ((float) ($receipt->{$field} ?? 0) === 0.0) {
            $receipt->{$field} = $plan['new_postage'];
            $receipt->save();
        }
        $count++;
    }
    return $count;
});

echo 'Updated genuine issued-letter events: '.$updated.PHP_EOL;
