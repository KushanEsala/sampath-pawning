<?php

namespace App\Services;

class SilverInterest
{
    /** A minimum first-period charge (up to periodDays, default 30), then daily proration at the same rate. */
    public static function amount(float $principal, float $monthlyRate, int $days, int $periodDays = 30): float
    {
        if ($principal <= 0 || $days <= 0 || $monthlyRate <= 0) {
            return 0.0;
        }

        $periodDays = $periodDays > 0 ? $periodDays : 30;

        if ($days <= $periodDays) {
            return ($principal / 100) * $monthlyRate;
        }

        $fullMonths = intdiv($days, $periodDays);
        $remainingDays = $days % $periodDays;
        $totalRate = ($monthlyRate * $fullMonths) + (($monthlyRate / $periodDays) * $remainingDays);

        return ($principal / 100) * $totalRate;
    }

    public static function validDays(?object $type): int
    {
        $configured = (int) ($type->validPeriod ?? 0);
        $period = (int) ($type->period3 ?? 0);
        return $configured > 0 ? $configured : ($period > 0 ? $period : 30);
    }
}
