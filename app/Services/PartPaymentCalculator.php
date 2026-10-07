<?php

namespace App\Services;

class PartPaymentCalculator
{
    public function calculate(
        float $principal,
        float $interest,
        float $serviceCharge,
        float $letterCharge,
        float $stampDuty,
        float $enteredPayment,
        float $discount = 0
    ): array {
        $principal = max(0, $principal);
        $discount = max(0, $discount);
        $chargesDue = max(0, $interest + $serviceCharge + $letterCharge + $stampDuty - $discount);
        $paymentReceived = max(0, $enteredPayment - $discount);
        $paidCharges = min($paymentReceived, $chargesDue);
        $interestAfterDiscount = max(0, $interest - min($interest, $discount));
        $paidInterest = min($interestAfterDiscount, $paidCharges);
        $capitalizedInterest = max(0, $interestAfterDiscount - $paidInterest);
        $principalPaid = min($principal, max(0, $paymentReceived - $chargesDue));
        $newPrincipal = max(0, $principal - $principalPaid + $capitalizedInterest);
        $unpaidCharges = max(0, $chargesDue - $paidCharges - $capitalizedInterest);

        return [
            'charges_due' => round($chargesDue, 2),
            'payment_received' => round($paymentReceived, 2),
            'redemption_total' => round($principal + $chargesDue, 2),
            'paid_charges' => round($paidCharges, 2),
            'paid_interest' => round($paidInterest, 2),
            'principal_paid' => round($principalPaid, 2),
            'new_principal' => round($newPrincipal, 2),
            'capitalized_interest' => round($capitalizedInterest, 2),
            'unpaid_charges' => round($unpaidCharges, 2),
        ];
    }
}
