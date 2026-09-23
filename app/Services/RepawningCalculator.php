<?php

namespace App\Services;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use Illuminate\Support\Collection;

class RepawningCalculator
{
    /**
     * Build the values displayed in the repawning month table.
     *
     * Karatage pawning rates are stored per eight grams, so each article's
     * lending value is (rate / 8) * weight. The available amount reduces by
     * one month's interest for every additional month selected.
     */
    public function preview(
        TPawnSum $receipt,
        Collection $pawnDetails,
        Collection $karatages,
        array $financial,
        ?Recei_Add $currentType,
        ?Recei_Add $repawnType
    ): array {
        $rates = $karatages->mapWithKeys(function ($row) {
            return [$this->normaliseKaratage($row->descrption ?? '') => (float) ($row->pawningrate ?? 0)];
        });

        $articleValue = $pawnDetails->sum(function ($detail) use ($rates) {
            $rate = (float) $rates->get($this->normaliseKaratage($detail->Karatage ?? ''), 0);

            return ($rate / 8) * (float) ($detail->Weight ?? 0);
        });

        $principal = (float) ($receipt->Pawn_Amount ?: $receipt->Amount ?: 0);
        $stampDuty = (float) ($currentType->stampduty ?? 0);
        $totalDue = $principal
            + (float) ($financial['interest'] ?? 0)
            + (float) ($financial['service_charge'] ?? 0)
            + (float) ($financial['letter_charge'] ?? 0)
            + $stampDuty;

        $availableAmount = $articleValue - $totalDue;
        $isSilver = strtoupper((string) ($receipt->receiptname ?: $receipt->Receipt_Type)) === 'SILVER';
        $monthlyRate = $isSilver
            ? (float) ($currentType->rate1 ?? $receipt->rate1 ?? 0)
            : (float) ($repawnType->rate3 ?? $currentType->rate3 ?? $receipt->rate3 ?? 0);
        $monthlyInterest = ($articleValue / 100) * $monthlyRate;
        $validMonths = $isSilver ? 1 : min(12, max(0, (int) ($receipt->Valid_Period ?? 0)));

        $monthOptions = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthOptions[$month] = $month <= $validMonths
                ? round($availableAmount - ($monthlyInterest * ($month - 1)), 2)
                : null;
        }

        return [
            'article_value' => round($articleValue, 2),
            'principal' => round($principal, 2),
            'stamp_duty' => round($stampDuty, 2),
            'available_amount' => round($availableAmount, 2),
            'monthly_rate' => round($monthlyRate, 4),
            'monthly_interest' => round($monthlyInterest, 2),
            'valid_months' => $validMonths,
            'month_options' => $monthOptions,
        ];
    }

    private function normaliseKaratage(mixed $value): string
    {
        return strtoupper(trim((string) $value));
    }
}
