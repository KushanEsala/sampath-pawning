<?php

// One-time, guarded repair of legacy letters left on receipts after part payment.
// Dry run: php database/manual/027_reconcile_stale_letter_payment_cycles.php
// Apply: php database/manual/027_reconcile_stale_letter_payment_cycles.php --apply
//   --backup=/absolute/private/full-backup.sql.gz --sha256=<verified backup hash>
// Never run a Laravel migration in this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$options = [];
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
        [$key, $value] = explode('=', substr($argument, 2), 2);
        $options[$key] = $value;
    }
}
if (DB::getDatabaseName() !== 'kreethya_sampath_prod_20261004') {
    throw new RuntimeException('Unexpected database target; no changes made.');
}

$latestTransactions = [];
foreach (DB::table('t_pawn_trans')
    ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
    ->orderByDesc('dDate')->orderByDesc('id')
    ->get(['id', 'BC', 'code', 'trans_type', 'dDate']) as $transaction) {
    $key = $transaction->BC.'|'.$transaction->code;
    $latestTransactions[$key] ??= $transaction;
}

$payments = [];
foreach (DB::table('t_pawn_payments')->get() as $payment) {
    $key = $payment->BC.'|'.$payment->Receipt_Number.'|'.substr((string) $payment->Redeem_Date, 0, 10);
    $payments[$key][] = $payment;
}

$eventReceipts = array_fill_keys(
    DB::table('arrears_letter_events')->distinct()->pluck('pawn_sum_id')->all(), true
);
$allocationTransactions = [];
foreach (DB::table('receipt_lifecycle_events')
    ->where('event_type', 'PART_PAYMENT_ALLOCATION')
    ->get(['pawn_sum_id', 'event_data']) as $event) {
    $data = json_decode((string) $event->event_data, true);
    if (isset($data['transaction_id'])) {
        $allocationTransactions[$event->pawn_sum_id.'|'.$data['transaction_id']] = true;
    }
}

$plans = [];
$skipped = [];
$skip = static function (string $reason) use (&$skipped): void {
    $skipped[$reason] = ($skipped[$reason] ?? 0) + 1;
};
foreach (TPawnSum::where('IsRedeemed', 0)->where('isForfeit', 0)->orderBy('id')->cursor() as $receipt) {
    $printed = [];
    foreach ([1, 2, 3] as $letterNo) {
        if ($receipt->{'letter_'.$letterNo.'_date'}) {
            $printed[] = $receipt->{'letter_'.$letterNo.'_date'}->toDateString();
        }
    }
    if (!$printed) continue;

    $lastPrint = max($printed);
    $cycleStart = ($receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date)?->toDateString();
    if (!$cycleStart || $lastPrint >= $cycleStart) continue;

    $key = $receipt->BC.'|'.$receipt->Receipt_Number;
    $transaction = $latestTransactions[$key] ?? null;
    if (!$transaction || substr((string) $transaction->dDate, 0, 10) < $lastPrint) {
        $skip('No later matching payment or repawn transaction');
        continue;
    }
    if ($transaction->trans_type !== 'PART_PAYMENT') {
        $skip('Latest cycle is a repawn; payment allocation is not comparable');
        continue;
    }

    $paymentDate = substr((string) $transaction->dDate, 0, 10);
    $matches = $payments[$key.'|'.$paymentDate] ?? [];
    if (count($matches) !== 1) {
        $skip('Missing or duplicate payment summary');
        continue;
    }
    $payment = $matches[0];
    $postage = round((float) $payment->Postage_Charges, 2);
    $cashRemainder = round((float) $payment->Payable_Total
        - (float) $payment->Paid_Interest - (float) $payment->Advance_Payment, 2);
    $capitalDelta = round((float) $receipt->Pawn_Amount
        - (float) $payment->Payable_Pawn_Amount, 2);
    if ($postage <= 0 || abs($cashRemainder) >= 0.01) {
        $skip('Postage missing or cash allocation is ambiguous');
        continue;
    }

    $alreadyCapitalized = abs($capitalDelta - $postage) < 0.01;
    $unpaid = abs($capitalDelta) < 0.01
        && abs((float) $receipt->BalanceInterest) < 0.01;
    if (!$alreadyCapitalized && !$unpaid) {
        $skip('Capital or carried balance requires individual review');
        continue;
    }
    if ($receipt->forfeit_queued_at
        || abs((float) $receipt->Pawn_Amount - (float) $receipt->RePawning_amount) >= 0.01
        || (float) $receipt->letter_pay_one > 0
        || (float) $receipt->letter_pay_two > 0
        || (float) $receipt->letter_pay_three > 0
        || isset($eventReceipts[$receipt->id])
        || isset($allocationTransactions[$receipt->id.'|'.$transaction->id])) {
        $skip('Queued, positive saved charge, event or inconsistent receipt');
        continue;
    }

    $newCapital = round((float) $payment->Payable_Pawn_Amount + $postage, 2);
    $plans[] = [
        'pawn_sum_id' => $receipt->id,
        'BC' => $receipt->BC,
        'receipt_number' => $receipt->Receipt_Number,
        'transaction_id' => $transaction->id,
        'payment_date' => $paymentDate,
        'payment_redeem_number' => $payment->Redeem_Number,
        'postage' => $postage,
        'kind' => $alreadyCapitalized ? 'already_capitalized' : 'unpaid_not_capitalized',
        'old_capital' => round((float) $receipt->Pawn_Amount, 2),
        'old_payment_balance' => round((float) $payment->Payable_Pawn_Amount, 2),
        'new_capital' => $newCapital,
        'receipt_updated_at' => (string) $receipt->getRawOriginal('updated_at'),
        'payment_updated_at' => (string) $payment->updated_at,
        'receipt_before' => $receipt->getAttributes(),
        'payment_before' => (array) $payment,
    ];
}

$byKind = array_count_values(array_column($plans, 'kind'));
ksort($byKind);
ksort($skipped);
$summary = [
    'mode' => $apply ? 'apply' : 'dry-run',
    'database' => DB::getDatabaseName(),
    'planned' => count($plans),
    'by_kind' => $byKind,
    'skipped' => $skipped,
    'total_postage' => round(array_sum(array_column($plans, 'postage')), 2),
    'capital_increase' => round(array_sum(array_map(
        fn ($row) => $row['new_capital'] - $row['old_capital'], $plans
    )), 2),
];
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
if (!$apply || !$plans) exit(0);

$fullBackup = $options['backup'] ?? '';
$backupHash = strtolower($options['sha256'] ?? '');
if (!str_starts_with($fullBackup, '/home/kreethya/db-backups/')
    || !is_file($fullBackup)
    || !preg_match('/^[a-f0-9]{64}$/', $backupHash)
    || hash_file('sha256', $fullBackup) !== $backupHash) {
    throw new RuntimeException('Verified private full backup is required; no changes made.');
}

$privateDir = storage_path('app');
$beforePath = $privateDir.'/letter_cycle_before_'.gmdate('Ymd_His').'_'.bin2hex(random_bytes(3)).'.json';
$before = [
    'full_backup' => $fullBackup,
    'full_backup_sha256' => $backupHash,
    'created_at_utc' => gmdate('c'),
    'summary' => $summary,
    'plans' => $plans,
];
if (file_put_contents($beforePath, json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
    throw new RuntimeException('Could not save row-level before-state; no changes made.');
}
chmod($beforePath, 0600);
echo json_encode(['before_state' => $beforePath, 'sha256' => hash_file('sha256', $beforePath)]).PHP_EOL;

$lifecycle = app(ReceiptLifecycleService::class);
$applied = 0;
$changed = [];
foreach ($plans as $plan) {
    $ok = DB::transaction(function () use ($plan, $lifecycle): bool {
        $receipt = TPawnSum::whereKey($plan['pawn_sum_id'])->lockForUpdate()->first();
        if (!$receipt || $receipt->BC !== $plan['BC']
            || (string) $receipt->Receipt_Number !== (string) $plan['receipt_number']
            || $receipt->IsRedeemed || $receipt->isForfeit || $receipt->forfeit_queued_at
            || (string) $receipt->getRawOriginal('updated_at') !== $plan['receipt_updated_at']
            || abs((float) $receipt->Pawn_Amount - $plan['old_capital']) >= 0.01
            || abs((float) $receipt->RePawning_amount - $plan['old_capital']) >= 0.01) {
            return false;
        }
        $latest = DB::table('t_pawn_trans')->where('BC', $plan['BC'])
            ->where('code', $plan['receipt_number'])
            ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
            ->orderByDesc('dDate')->orderByDesc('id')->first(['id']);
        if (!$latest || (int) $latest->id !== (int) $plan['transaction_id']) return false;

        $query = DB::table('t_pawn_payments')->where('BC', $plan['BC'])
            ->where('Receipt_Number', $plan['receipt_number'])
            ->whereDate('Redeem_Date', $plan['payment_date'])
            ->where('Redeem_Number', $plan['payment_redeem_number']);
        $payments = (clone $query)->lockForUpdate()->get();
        if ($payments->count() !== 1) return false;
        $payment = $payments->first();
        if ((string) $payment->updated_at !== $plan['payment_updated_at']
            || abs((float) $payment->Payable_Pawn_Amount - $plan['old_payment_balance']) >= 0.01
            || abs((float) $payment->Postage_Charges - $plan['postage']) >= 0.01
            || abs(round((float) $payment->Payable_Total
                - (float) $payment->Paid_Interest - (float) $payment->Advance_Payment, 2)) >= 0.01) {
            return false;
        }
        if (DB::table('arrears_letter_events')->where('pawn_sum_id', $receipt->id)->exists()) return false;
        if (DB::table('receipt_lifecycle_events')->where('pawn_sum_id', $receipt->id)
            ->where('event_type', 'PART_PAYMENT_ALLOCATION')
            ->where('event_data', 'like', '%"transaction_id":'.$plan['transaction_id'].'%')
            ->exists()) return false;

        $snapshots = [];
        foreach ([1, 2, 3] as $number) {
            $date = $receipt->{'letter_'.$number.'_date'};
            if ($date) {
                $snapshots[] = [
                    'letter_no' => $number,
                    'printed_at' => $date->toDateString(),
                    'postage_charge' => null,
                ];
            }
        }
        if (!$snapshots || max(array_column($snapshots, 'printed_at'))
            >= ($receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date)->toDateString()) {
            return false;
        }

        if ((clone $query)->update([
            'Payable_Pawn_Amount' => $plan['new_capital'],
            'updated_at' => now(),
        ]) !== 1) {
            throw new RuntimeException('Payment row changed during repair.');
        }
        if ($plan['kind'] === 'unpaid_not_capitalized') {
            $receipt->Pawn_Amount = $plan['new_capital'];
            $receipt->RePawning_amount = $plan['new_capital'];
            $receipt->save();
        }
        $lifecycle->record(
            $receipt->fresh(), 'PART_PAYMENT_ALLOCATION', $plan['postage'],
            'Historical printed-letter cycle reconciled from the posted payment.',
            [
                'transaction_id' => $plan['transaction_id'],
                'payment_date' => $plan['payment_date'],
                'recorded_postage_charge_total' => $plan['postage'],
                'capitalized_charges' => $plan['postage'],
                'new_capital' => $plan['new_capital'],
                'previous_capital' => $plan['old_capital'],
                'previous_payment_payable_pawn_amount' => $plan['old_payment_balance'],
                'cleared_letter_snapshots' => $snapshots,
                'repair_source' => '027_reconcile_stale_letter_payment_cycles',
            ]
        );
        $lifecycle->reactivate(
            $receipt->fresh(), (float) $payment->Payable_Total,
            'Historical payment reconciled; old printed-letter cycle closed.'
        );
        return true;
    }, 3);
    if ($ok) $applied++;
    else $changed[] = ['BC' => $plan['BC'], 'receipt_number' => $plan['receipt_number'], 'pawn_sum_id' => $plan['pawn_sum_id']];
}
echo json_encode([
    'applied' => $applied,
    'skipped_due_to_concurrent_change' => $changed,
    'before_state' => $beforePath,
    'before_state_sha256' => hash_file('sha256', $beforePath),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
