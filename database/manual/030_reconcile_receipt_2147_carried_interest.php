<?php
// Branch 001 receipt 2147 only. Dry run by default; use --apply for the guarded correction.
// Never run Laravel migrations in this project.
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
ini_set('serialize_precision', '-1');

use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$same = fn($actual, float $expected) => abs((float) $actual - $expected) < 0.005;
$receiptQuery = fn() => DB::table('t_pawn_sums')->where('BC', '001')->where('Receipt_Number', '2147');
$transactionQuery = fn(int $id) => DB::table('t_pawn_trans')->where('id', $id)
    ->where('BC', '001')->where('code', '2147')->where('trans_type', 'PART_PAYMENT');
$paymentQuery = fn(int $number) => DB::table('t_pawn_payments')->where('BC', '001')
    ->where('Receipt_Number', '2147')->where('Redeem_Number', $number);

$inspect = function (bool $lock) use ($same, $receiptQuery, $transactionQuery, $paymentQuery): array {
    $first = fn($query) => $lock ? $query->lockForUpdate()->first() : $query->first();
    $receipt = $first($receiptQuery());
    $sepFirst = $first($transactionQuery(37231));
    $sepSecond = $first($transactionQuery(37232));
    $november = $first($transactionQuery(46878));
    $sepFirstPayment = $first($paymentQuery(4075));
    $sepSecondPayment = $first($paymentQuery(4076));
    $novemberPayment = $first($paymentQuery(5962));
    $event = $first(DB::table('receipt_lifecycle_events')->where('id', 8976)
        ->where('pawn_sum_id', 4250)->where('event_type', 'PART_PAYMENT_ALLOCATION'));
    foreach (compact('receipt', 'sepFirst', 'sepSecond', 'november', 'sepFirstPayment',
        'sepSecondPayment', 'novemberPayment', 'event') as $name => $row) {
        if (!$row) throw new RuntimeException("Missing {$name}; no data changed.");
    }
    if ($receipt->id != 4250 || $receipt->IsRedeemed || $receipt->isForfeit
        || !$same($receipt->Pawn_Amount, 92770.66) || !$same($receipt->RePawning_amount, 92770.66)
        || !$same($receipt->BalanceInterest, 0)
        || substr((string) $receipt->RePawning_date, 0, 10) !== '2026-11-04') {
        throw new RuntimeException('Receipt changed since audit; no data changed.');
    }
    if (!$same($sepFirst->Paided_Interest, 5446.94)
        || !$same($sepFirst->interest_Balance, 3446.94)
        || !$same($sepFirst->payable_total, 2000)
        || !$same($sepSecond->Paided_Interest, 0)
        || !$same($sepSecond->interest_Balance, 0)
        || !$same($sepSecond->payable_total, 1000)
        || !$same($november->Paided_Interest, 2000)
        || !$same($november->trans_amount, 94770.66)
        || !$same($november->payable_total, 2000)) {
        throw new RuntimeException('Payment transactions changed since audit; no data changed.');
    }
    if ($paymentQuery(4075)->count() !== 1 || $paymentQuery(4076)->count() !== 1
        || $paymentQuery(5962)->count() !== 1
        || !$same($sepFirstPayment->Paid_Interest, 5446.94)
        || !$same($sepFirstPayment->Payable_Interest, 5446.94)
        || !$same($sepSecondPayment->Paid_Interest, 0)
        || !$same($novemberPayment->Payable_Pawn_Amount, 92770.66)
        || !$same($novemberPayment->paid_cap_amount, 94770.66)) {
        throw new RuntimeException('Payment summaries changed since audit; no data changed.');
    }
    $data = json_decode((string) $event->event_data, true, 512, JSON_THROW_ON_ERROR);
    if (($data['transaction_id'] ?? null) != 46878
        || !$same($data['interest_due'] ?? null, 3771.28)
        || !$same($data['capitalized_interest'] ?? null, 1771.28)
        || !$same($data['capitalized_charges'] ?? null, 300)
        || !$same($data['new_capital'] ?? null, 92770.66)
        || !$same($data['charges_due'] ?? null, 4071.28)) {
        throw new RuntimeException('Allocation changed since audit; no data changed.');
    }
    foreach ([4075 => 2000, 4076 => 1000, 5962 => 2000] as $number => $cash) {
        $posted = DB::table('t_account_trans')->where('branch_code', '001')
            ->where('related_type', 'PART_PAYMENT')->where('related_id', $number);
        if ($posted->count() !== 1 || !$same($posted->sum('dr'), $cash)) {
            throw new RuntimeException("Cash for payment {$number} changed; no data changed.");
        }
    }
    if (DB::table('t_pawn_trans')->where('BC', '001')->where('code', '2147')
        ->whereDate('dDate', '>', '2026-11-03')->exists()) {
        throw new RuntimeException('A later loan transaction exists; no data changed.');
    }
    if (DB::table('receipt_lifecycle_events')->where('pawn_sum_id', 4250)
        ->where('event_type', 'MANUAL_RECONCILIATION')
        ->where('event_data', 'like', '%030_reconcile_receipt_2147_carried_interest%')->exists()) {
        throw new RuntimeException('This correction was already applied.');
    }
    return ['receipt' => $receipt, 'sep_first' => $sepFirst, 'sep_second' => $sepSecond,
        'november' => $november, 'sep_first_payment' => $sepFirstPayment,
        'november_payment' => $novemberPayment, 'event' => $event, 'event_data' => $data];
};

$plan = [
    'database' => DB::getDatabaseName(), 'mode' => $apply ? 'APPLY' : 'DRY_RUN',
    'branch' => '001', 'receipt' => '2147', 'cash_change' => 0,
    'unpaid_interest_from_2026_09_01' => 3446.94,
    'current_capital_before' => 92770.66, 'current_capital_after' => 96217.60,
    'first_september_interest_paid_after' => 2000.00,
    'second_september_carried_interest_after' => 3446.94,
    'november_3_current_period_interest_due' => 3771.28,
    'november_3_total_unpaid_interest_capitalized_after' => 5218.22,
    'november_3_postage_capitalized' => 300.00,
];

if (!$apply) {
    $inspect(false);
    echo json_encode($plan, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
}

DB::transaction(function () use ($inspect, $receiptQuery, $transactionQuery, $paymentQuery): void {
    $before = $inspect(true);
    $now = now();

    $transactionQuery(37231)->update(['Paided_Interest' => 2000.00]);
    $paymentQuery(4075)->update(['Paid_Interest' => 2000.00, 'Payable_Interest' => 2000.00]);
    $transactionQuery(37232)->update(['interest_Balance' => 3446.94]);

    $receiptQuery()->update(['Pawn_Amount' => 96217.60, 'RePawning_amount' => 96217.60]);
    $transactionQuery(46878)->update(['trans_amount' => 98217.60]);
    $paymentQuery(5962)->update(['Payable_Pawn_Amount' => 96217.60,
        'paid_cap_amount' => 98217.60]);

    DB::table('receipt_lifecycle_events')->insert([
        'pawn_sum_id' => 4250, 'BC' => '001', 'receipt_number' => '2147',
        'event_type' => 'PART_PAYMENT_ALLOCATION', 'event_date' => $now,
        'amount' => 0, 'description' => 'Recorded the 1 September part-payment interest due, paid, and carried.',
        'event_data' => json_encode([
            'transaction_id' => 37231, 'payment_date' => '2026-09-01',
            'interest_due' => 5446.94, 'paid_interest' => 2000.00,
            'unpaid_interest_carried' => 3446.94,
            'capitalized_interest' => 0, 'capitalized_charges' => 0,
            'charges_due' => 5446.94, 'paid_capital' => 0,
            'new_capital' => 91699.38, 'discount' => 0,
            'repair_source' => '030_reconcile_receipt_2147_carried_interest',
        ], JSON_THROW_ON_ERROR),
        'created_by' => 'manual-reconciliation', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $data = $before['event_data'];
    $data['carried_interest_from_2026_09_01'] = 3446.94;
    $data['total_interest_due_including_carry'] = 7218.22;
    $data['capitalized_interest'] = 5218.22;
    $data['charges_due'] = 7518.22;
    $data['new_capital'] = 96217.60;
    $data['repair_source'] = '030_reconcile_receipt_2147_carried_interest';
    DB::table('receipt_lifecycle_events')->where('id', 8976)->update([
        'amount' => 5518.22, 'event_data' => json_encode($data, JSON_THROW_ON_ERROR),
        'updated_at' => $now,
    ]);

    DB::table('receipt_lifecycle_events')->insert([
        'pawn_sum_id' => 4250, 'BC' => '001', 'receipt_number' => '2147',
        'event_type' => 'MANUAL_RECONCILIATION', 'event_date' => $now,
        'amount' => 0,
        'description' => 'Restored Rs. 3,446.94 unpaid September interest to current capital; cash postings unchanged.',
        'event_data' => json_encode([
            'repair_source' => '030_reconcile_receipt_2147_carried_interest',
            'september_first_transaction' => 37231, 'september_second_transaction' => 37232,
            'november_transaction' => 46878, 'prior_capital' => 92770.66,
            'corrected_capital' => 96217.60, 'cash_change' => 0,
        ], JSON_THROW_ON_ERROR),
        'created_by' => 'manual-reconciliation', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

echo json_encode($plan + ['status' => 'APPLIED'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
