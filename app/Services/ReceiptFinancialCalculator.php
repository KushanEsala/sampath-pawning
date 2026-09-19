<?php

namespace App\Services;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use Carbon\Carbon;

class ReceiptFinancialCalculator
{
    public function __construct(
        private ?ReceiptTypeResolver $resolver = null
    ) {
        $this->resolver = $resolver ?? app(ReceiptTypeResolver::class);
    }

    public function calculate(TPawnSum $receipt, Carbon|string|null $calculationDate = null): array
    {
        $asOf = $calculationDate instanceof Carbon
            ? $calculationDate->copy()->startOfDay()
            : Carbon::parse($calculationDate ?? now())->startOfDay();

        $days = ReceiptInterestPeriod::days($receipt, $asOf);
        $configurationFields = ['period1', 'period2', 'validPeriod', 'rate1', 'rate2', 'rate3', 'service_charge'];
        $needsConfigurationFallback = collect($configurationFields)
            ->contains(fn (string $field) => $receipt->{$field} === null);
        $config = $needsConfigurationFallback
            ? $this->resolver->resolveForReceipt($receipt)
            : null;

        $receiptName = strtoupper((string) ($receipt->receiptname ?: $receipt->Receipt_Type));
        $principal = (float) ($receipt->Pawn_Amount ?: $receipt->Amount ?: 0);
        $period1 = (int) $this->configuredValue($receipt, $config, 'period1', 0);
        $period2 = (int) $this->configuredValue($receipt, $config, 'period2', 0);
        $validPeriod = (int) $this->configuredValue($receipt, $config, 'validPeriod', $receipt->Valid_Period ?? 0);
        $rate1 = (float) $this->configuredValue($receipt, $config, 'rate1', 0);
        $rate2 = (float) $this->configuredValue($receipt, $config, 'rate2', 0);
        $rate3 = (float) $this->configuredValue($receipt, $config, 'rate3', 0);

        $grossInterest = $this->interestForDays(
            $receiptName, $principal, $days, $period1, $period2, $validPeriod,
            $rate1, $rate2, $rate3
        );

        $paidInterest = (float) ($receipt->interest_Paid ?? 0);
        $carriedInterest = (float) ($receipt->BalanceInterest ?? 0);
        $interest = max(0, $grossInterest - $paidInterest + $carriedInterest);
        $serviceCharge = (float) $this->configuredValue($receipt, $config, 'service_charge', 0);
        $letterCharges = 0.0;
        foreach ([1, 2, 3] as $letterNo) {
            $payField = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$letterNo - 1];
            if (!empty($receipt->{"letter_{$letterNo}_date"}) || (bool) $receipt->{"is_letter_{$letterNo}"} || (float) ($receipt->{$payField} ?? 0) > 0) {
                $letterCharges += $this->resolveLetterCharge($receipt, $letterNo);
            }
        }
        $arrearsTotal = $interest + $serviceCharge + $letterCharges;

        return [
            'calculation_date' => $asOf->toDateString(),
            'days' => $days,
            'principal' => round($principal, 2),
            'gross_interest' => round($grossInterest, 2),
            'paid_interest' => round($paidInterest, 2),
            'carried_interest' => round($carriedInterest, 2),
            'interest' => round($interest, 2),
            'service_charge' => round($serviceCharge, 2),
            'letter_charge' => round($letterCharges, 2),
            'arrears_total' => round($arrearsTotal, 2),
            'redemption_total' => round($principal + $arrearsTotal, 2),
        ];
    }

    public function resolveLetterCharge(TPawnSum $receipt, int $letterNo): float
    {
        $field = ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$letterNo - 1] ?? null;
        if ($field && (float) ($receipt->{$field} ?? 0) > 0) {
            return round((float) $receipt->{$field}, 2);
        }

        // Check if t_pawn_trans has recorded Postage_charge
        $transPostage = (float) \Illuminate\Support\Facades\DB::table('t_pawn_trans')
            ->where('code', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)
            ->whereNotNull('Postage_charge')
            ->where('Postage_charge', '>', 0)
            ->orderByDesc('id')
            ->value('Postage_charge');

        $letterCount = 0;
        foreach ([1, 2, 3] as $num) {
            if (!empty($receipt->{"letter_{$num}_date"}) || (bool) $receipt->{"is_letter_{$num}"}) {
                $letterCount++;
            }
        }

        if ($transPostage > 0 && $letterCount > 0) {
            return round($transPostage / $letterCount, 2);
        }

        if ((float) ($receipt->Postage_charge ?? 0) > 0) {
            return round((float) $receipt->Postage_charge, 2);
        }

        return $this->postageCharge($receipt);
    }

    public function postageCharge(TPawnSum $receipt): float
    {
        if ($receipt->Postage_charge !== null && (float) $receipt->Postage_charge > 0) {
            return round((float) $receipt->Postage_charge, 2);
        }

        $config = $this->resolver->resolveForReceipt($receipt);
        return round((float) ($config->Postage_charge ?? 0), 2);
    }

    private function configuredValue(TPawnSum $receipt, ?Recei_Add $config, string $field, mixed $default): mixed
    {
        return $receipt->{$field} !== null ? $receipt->{$field} : ($config->{$field} ?? $default);
    }

    private function interestForDays(
        string $receiptName,
        float $principal,
        int $days,
        int $period1,
        int $period2,
        int $validPeriod,
        float $rate1,
        float $rate2,
        float $rate3
    ): float {
        if ($days <= 0 || $principal <= 0) {
            return 0;
        }

        $months = (int) ceil($days / 30);

        if ($receiptName === 'SILVER') {
            return ($principal / 100) * $rate1 * $months;
        }

        if ($receiptName === 'D' && $validPeriod > 0 && $days > $validPeriod) {
            $penaltyMonths = (int) ceil(($days - $validPeriod) / 30);
            return (($principal / 100) * $rate2 * $months)
                + (($principal / 100) * 0.5 * $penaltyMonths);
        }

        if ($days <= $period1) {
            return ($principal / 100) * $rate1;
        }

        if ($days <= $period2) {
            return ($principal / 100) * $rate2;
        }

        if ($days <= 30) {
            return ($principal / 100) * $rate3;
        }

        $fullMonths = intdiv($days, 30);
        $remainingDays = $days % 30;
        $totalRate = ($rate3 * $fullMonths) + (($rate3 / 30) * $remainingDays);

        return ($principal / 100) * $totalRate;
    }
}
