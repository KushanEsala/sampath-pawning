<?php

// One-time, guarded production data repair. Dry-run by default:
// php database/manual/024_reconcile_receipt_256_letter_cycle.php
// php database/manual/024_reconcile_receipt_256_letter_cycle.php --apply
// Never run Laravel migrations for this project.

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TPawnSum;
use App\Services\ReceiptLifecycleService;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);

DB::transaction(function () use ($apply): void {
    $receipt = TPawnSum::where('id', 480)->where('BC', '001')
        ->where('Receipt_Number', '256')->lockForUpdate()->firstOrFail();

    $alreadyRepaired = DB::table('receipt_lifecycle_events')
        ->where('pawn_sum_id', 480)->where('event_type', 'PART_PAYMENT_ALLOCATION')
        ->where('description', 'Historical receipt 256 postage and letter-cycle reconciliation.')
        ->exists();
    if ($alreadyRepaired) {
        if (abs((float) $receipt->Pawn_Amount - 97148.10) > 0.01 || $receipt->is_letter_1) {
            throw new RuntimeException('Receipt 256 has a partial or conflicting repair; stop for review.');
        }
        echo "Receipt 256 was already reconciled; no changes made.\n";
        return;
    }

    $paymentQuery = DB::table('t_pawn_payments')->where('BC', '001')
        ->where('Receipt_Number', '256')->where('Redeem_Number', 2363)
        ->whereDate('Redeem_Date', '2026-07-07');
    if ((clone $paymentQuery)->count() !== 1) {
        throw new RuntimeException('Expected exactly one July 7 payment summary for receipt 256.');
    }
    $payment = $paymentQuery->lockForUpdate()->first();
    $transaction = DB::table('t_pawn_trans')->where('id', 28027)
        ->where('BC', '001')->where('code', '256')->lockForUpdate()->first();

    $expect = static function (bool $condition, string $message): void {
        if (!$condition) throw new RuntimeException('Receipt 256 changed: '.$message);
    };
    $same = static fn ($actual, float $expected): bool => abs((float) $actual - $expected) < 0.01;

    $expect(!$receipt->IsRedeemed && !$receipt->isForfeit, 'receipt is closed');
    $expect($same($receipt->Pawn_Amount, 96548.10)
        && $same($receipt->RePawning_amount, 96548.10), 'current capital differs');
    $expect($same($receipt->BalanceInterest, 0), 'carried interest differs');
    $expect($receipt->Pawn_Date?->toDateString() === '2026-07-08'
        && $receipt->Final_date?->toDateString() === '2026-08-07', 'payment cycle dates differ');
    $expect((bool) $receipt->is_letter_1 && (bool) $receipt->is_letter_2
        && (bool) $receipt->is_letter_3, 'printed-letter flags differ');
    $printedDates = [
        $receipt->letter_1_date?->toDateString(),
        $receipt->letter_2_date?->toDateString(),
        $receipt->letter_3_date?->toDateString(),
    ];
    $expect($printedDates === ['2026-04-20', '2026-05-05', '2026-06-02'], 'letter print dates differ');
    $expect($payment && $same($payment->Payable_Total, 9265)
        && $same($payment->Paid_Interest, 8663.10)
        && $same($payment->Postage_Charges, 600)
        && $same($payment->Advance_Payment, 601.90)
        && $same($payment->Original_Pawn_Amount, 97150)
        && $same($payment->Payable_Pawn_Amount, 96548.10), 'payment allocation differs');
    $expect($transaction && $transaction->trans_type === 'PART_PAYMENT'
        && substr((string) $transaction->dDate, 0, 10) === '2026-07-07'
        && $same($transaction->Paided_Interest, 8663.10)
        && $same($transaction->Paided_Captional, 601.90), 'transaction differs');
    $expect(!DB::table('t_pawn_trans')->where('BC', '001')->where('code', '256')
        ->where('dDate', '>', '2026-07-07')->exists(), 'a later transaction exists');
    $expect(!DB::table('arrears_letter_events')->where('pawn_sum_id', 480)->exists(),
        'letter events require separate reconciliation');

    // The posted cash went to interest and capital, leaving the recorded
    // postage unpaid. Capitalize only the actual total stored on the payment;
    // no historical per-letter price or print event is inferred.
    $correctedCapital = round((float) $payment->Payable_Pawn_Amount
        + (float) $payment->Postage_Charges, 2);
    $expect($same($correctedCapital, 97148.10)
        && $same((float) $payment->Original_Pawn_Amount
            - (float) $payment->Advance_Payment
            + (float) $payment->Postage_Charges, $correctedCapital), 'capital arithmetic differs');

    echo "Receipt 001/256: payment 2026-07-07, postage Rs. 600.00 unpaid; "
        ."capital Rs. 96,548.10 -> Rs. 97,148.10; restart at first letter after 2026-08-07 expiry.\n";
    if (!$apply) {
        echo "Dry run only. Pass --apply to perform the guarded transaction.\n";
        return;
    }

    $paymentQuery->update(['Payable_Pawn_Amount' => $correctedCapital]);
    $receipt->Pawn_Amount = $correctedCapital;
    $receipt->RePawning_amount = $correctedCapital;
    $receipt->save();

    $lifecycle = app(ReceiptLifecycleService::class);
    $lifecycle->record(
        $receipt->fresh(), 'PART_PAYMENT_ALLOCATION', 600,
        'Historical receipt 256 postage and letter-cycle reconciliation.',
        [
            'transaction_id' => 28027,
            'payment_date' => '2026-07-07',
            'payment_operator' => $transaction->OC,
            'recorded_postage_charge_total' => 600,
            'interest_due' => 8663.10,
            'paid_interest' => 8663.10,
            'capitalized_interest' => 0,
            'capitalized_charges' => 600,
            'paid_capital' => 601.90,
            'new_capital' => $correctedCapital,
            'previous_capital' => 96548.10,
            'previous_payment_payable_pawn_amount' => 96548.10,
            'cleared_letter_snapshots' => [
                ['letter_no' => 1, 'printed_at' => $printedDates[0], 'postage_charge' => null],
                ['letter_no' => 2, 'printed_at' => $printedDates[1], 'postage_charge' => null],
                ['letter_no' => 3, 'printed_at' => $printedDates[2], 'postage_charge' => null],
            ],
        ]
    );
    $lifecycle->reactivate(
        $receipt->fresh(), 9265,
        'Historical July 7 payment reconciled; previous printed-letter cycle closed.'
    );

    echo "Reconciled receipt 001/256 atomically. Original print dates are retained in the audit event.\n";
});
