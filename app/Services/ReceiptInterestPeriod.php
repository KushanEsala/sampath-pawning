<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReceiptInterestPeriod
{
    public static function days(Model $receipt, Carbon|string|null $date = null): int
    {
        $asOf = Carbon::parse($date ?? now())->startOfDay();
        $start = Carbon::parse($receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date)->startOfDay();

        return max(0, $start->diffInDays($asOf, false) + 1);
    }
}
