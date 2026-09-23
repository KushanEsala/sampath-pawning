<?php

namespace App\Services;

use App\Models\TOpeningPawnSum;
use App\Models\TPawnSum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ReceiptPaymentEligibility
{
    public static function query(string $type, string $branch): Builder
    {
        $model = match ($type) {
            'Pawn' => TPawnSum::class,
            'Opening_Pawn' => TOpeningPawnSum::class,
            default => throw ValidationException::withMessages(['receipt_number'=>'Invalid receipt type.']),
        };

        return $model::where('BC', $branch)->where('IsRedeemed', 0)->where('isForfeit', 0);
    }

    public static function lock(string $type, mixed $number, string $branch): Model
    {
        $receipt = self::query($type, $branch)->where('Receipt_Number', $number)->lockForUpdate()->first();
        if (!$receipt) {
            throw ValidationException::withMessages(['receipt_number'=>'Receipt unavailable: it may be redeemed, forfeited, or belong to another branch. Search for an active receipt again.']);
        }
        return $receipt;
    }

    public static function lockForRedemption(string $type, mixed $number, string $branch): Model
    {
        $receipt = self::lock($type, $number, $branch);
        self::assertRedemptionAllowed($receipt);

        return $receipt;
    }

    public static function assertRedemptionAllowed(Model $receipt): void
    {
        if ((bool) ($receipt->is_blocked ?? false)) {
            throw ValidationException::withMessages([
                'receipt_number' => 'This receipt is blocked. An administrator must unblock it before redemption.',
            ]);
        }
    }
}
