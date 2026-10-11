<?php

// One-time, guarded reversal of the Rs. 10 test payment on branch 001 receipt 7828.
// Dry-run: php database/manual/026_revert_test_part_payment_7828.php
// Apply:   php database/manual/026_revert_test_part_payment_7828.php --apply
// Never run Laravel migrations for this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$expect = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Receipt 001/7828 changed; reversal stopped: '.$message);
    }
};
$same = static fn ($actual, float $expected): bool => abs((float) $actual - $expected) < 0.005;

DB::transaction(function () use ($apply, $expect, $same): void {
    $receipt = DB::table('t_pawn_sums')->where('id', 13322)
        ->where('BC', '001')->where('Receipt_Number', 7828)->lockForUpdate()->first();
    $expect($receipt !== null, 'receipt not found');

    $paymentQuery = DB::table('t_pawn_payments')->where('BC', '001')
        ->where('Receipt_Number', 7828)->where('Redeem_Number', 5255)
        ->where('created_at', '2026-10-11 23:09:42');
    $payment = (clone $paymentQuery)->lockForUpdate()->first();
    $transaction = DB::table('t_pawn_trans')->where('id', 43402)
        ->where('BC', '001')->where('code', 7828)->lockForUpdate()->first();
    $account = DB::table('t_account_trans')->where('id', 43840)
        ->where('branch_code', '001')->where('related_type', 'PART_PAYMENT')
        ->where('related_id', 5255)->lockForUpdate()->first();
    $event = DB::table('receipt_lifecycle_events')->where('id', 1957)
        ->where('pawn_sum_id', 13322)->lockForUpdate()->first();
    $detail = DB::table('t_pawn_details')->where('id', 14368)
        ->where('BC', '001')->where('Receipt_Number', 7828)->lockForUpdate()->first();

    if ($payment === null && $transaction === null && $account === null && $event === null
        && $same($receipt->Pawn_Amount, 136191)
        && (string) $receipt->Pawn_Date === '2025-10-20'
        && (string) $receipt->Final_date === '2025-12-20') {
        echo "Receipt 001/7828: test payment was already reversed; no changes made.\n";
        return;
    }

    $expect((clone $paymentQuery)->count() === 1, 'test payment is not unique');
    $expect($payment !== null && $same($payment->Payable_Total, 10)
        && $same($payment->Paid_Interest, 10)
        && $same($payment->Original_Pawn_Amount, 136191)
        && $same($payment->Payable_Pawn_Amount, 166811.72)
        && $same($payment->Advance_Payment, 0), 'payment values differ');
    $expect($transaction !== null && $transaction->trans_type === 'PART_PAYMENT'
        && (int) $transaction->trans_no === 5255
        && $same($transaction->Dr_amount, 10)
        && $same($transaction->Paided_Interest, 10)
        && $same($transaction->Paided_Captional, 0), 'pawn transaction differs');
    $expect($account !== null && $same($account->dr, 10)
        && $same($account->cr, 0), 'cash accounting entry differs');
    $eventData = $event ? json_decode((string) $event->event_data, true) : null;
    $expect($event !== null && $event->event_type === 'PART_PAYMENT_ALLOCATION'
        && is_array($eventData) && (int) ($eventData['transaction_id'] ?? 0) === 43402
        && $same($eventData['capitalized_interest'] ?? 0, 30620.72), 'allocation event differs');
    $expect($detail !== null && (string) $detail->updated_at === '2026-10-11 23:09:42'
        && !(int) $detail->IsRedeemed && !(int) $detail->isForfeit, 'article changed');
    $expect(!(int) $receipt->IsRedeemed && !(int) $receipt->isForfeit
        && $same($receipt->Pawn_Amount, 166811.72)
        && $same($receipt->RePawning_amount, 166811.72)
        && $same($receipt->Advance_Payment, 0)
        && $same($receipt->interest_Paid, 0)
        && $same($receipt->BalanceInterest, 0)
        && (string) $receipt->Pawn_Date === '2026-10-12'
        && (string) $receipt->RePawning_date === '2026-10-12'
        && (string) $receipt->Final_date === '2026-11-11'
        && (int) $receipt->Valid_Period === 1
        && (string) $receipt->updated_at === '2026-10-11 23:09:42', 'current receipt values differ');
    $expect((string) $receipt->To_Date === '2025-11-19'
        && (string) $receipt->Receipt_Type === 'C'
        && (string) $receipt->receiptname === 'C', 'receipt identity or type differs');
    $expect(!DB::table('t_pawn_trans')->where('BC', '001')->where('code', 7828)
        ->where('id', '>', 43402)->exists(), 'later pawn transaction exists');
    $expect(!DB::table('t_pawn_payments')->where('BC', '001')
        ->where('Receipt_Number', 7828)->where('created_at', '>', '2026-10-11 23:09:42')
        ->exists(), 'later payment exists');
    $expect(!DB::table('receipt_lifecycle_events')->where('pawn_sum_id', 13322)
        ->where('id', '>', 1957)->exists(), 'later lifecycle event exists');
    $expect(!DB::table('arrears_letter_events')->where('pawn_sum_id', 13322)->exists(),
        'letter event exists');
    $expect(!DB::table('forfeit_reminder_promises')->where('pawn_sum_id', 13322)->exists(),
        'forfeit promise exists');

    echo "Receipt 001/7828: Rs. 10.00 test payment #5255; capital Rs. 166,811.72 -> Rs. 136,191.00.\n";
    if (!$apply) {
        echo "Dry run only. Pass --apply to back up and reverse the exact transaction.\n";
        return;
    }

    // Retain the exact affected rows before deleting or changing anything.
    $backupDir = storage_path('app/manual-reversals');
    if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true) && !is_dir($backupDir)) {
        throw new RuntimeException('Could not create private reversal backup directory.');
    }
    $backupPath = $backupDir.'/receipt_001_7828_before_'.date('Ymd_His').'.json';
    $backup = json_encode([
        'reason' => 'User-requested reversal of Rs. 10 test part payment on 2026-10-11',
        'receipt' => $receipt,
        'article' => $detail,
        'payment' => $payment,
        'pawn_transaction' => $transaction,
        'cash_account_entry' => $account,
        'lifecycle_event' => $event,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (file_put_contents($backupPath, $backup, LOCK_EX) === false || !chmod($backupPath, 0600)) {
        throw new RuntimeException('Could not save private reversal backup.');
    }

    // Values below are the receipt's exact pre-payment values from the
    // 2026-10-11 22:57:36 production SQL backup; no rate is recalculated.
    $changed = DB::table('t_pawn_sums')->where('id', 13322)->update([
        'Pawn_Amount' => 136191.00,
        'RePawning_amount' => 136191.00,
        'Advance_Payment' => null,
        'interest_Paid' => null,
        'BalanceInterest' => null,
        'Pawn_Date' => '2025-10-20',
        'Valid_Period' => 2,
        'Final_date' => '2025-12-20',
        'RePawning_date' => '2025-10-20',
        'updated_at' => '2026-10-07 19:07:41',
    ]);
    $expect($changed === 1, 'receipt update did not affect exactly one row');
    $changed = DB::table('t_pawn_details')->where('id', 14368)
        ->update(['updated_at' => '2026-06-28 06:21:40']);
    $expect($changed === 1, 'article timestamp update did not affect exactly one row');
    $expect($paymentQuery->delete() === 1, 'payment delete did not affect exactly one row');
    $expect(DB::table('t_pawn_trans')->where('id', 43402)->delete() === 1,
        'pawn transaction delete did not affect exactly one row');
    $expect(DB::table('t_account_trans')->where('id', 43840)->delete() === 1,
        'cash account entry delete did not affect exactly one row');
    $expect(DB::table('receipt_lifecycle_events')->where('id', 1957)->delete() === 1,
        'allocation event delete did not affect exactly one row');

    echo "Reversed atomically. Private current-row backup: {$backupPath}\n";
});
