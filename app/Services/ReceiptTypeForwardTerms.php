<?php

namespace App\Services;

use App\Models\Recei_Add;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Non-interest terms apply to open receipts from a type edit onward. */
class ReceiptTypeForwardTerms
{
    public const FIELDS = [
        'service_charge', 'Postage_charge', 's_charge_less', 's_charge_greater',
        'letter_1_days', 'letter_2_days', 'letter_3_days', 'forfeit_reminder_days',
    ];

    public function openReceipts(string $typeName): Builder
    {
        return DB::table('t_pawn_sums')
            ->whereRaw("UPPER(TRIM(COALESCE(NULLIF(receiptname, ''), Receipt_Type))) = ?", [strtoupper(trim($typeName))])
            ->whereRaw('COALESCE(IsRedeemed, 0) = 0')
            ->whereRaw('COALESCE(isForfeit, 0) = 0');
    }

    public function sync(Recei_Add $type, ?string $previousName = null): int
    {
        $values = $this->values($type);

        $names = array_unique(array_filter([$type->receiptname, $previousName]));
        $updated = 0;
        foreach ($names as $name) {
            // DB query builder intentionally leaves receipt updated_at, letter
            // events, payments and interest-rate snapshots untouched.
            $updated += $this->mismatches($name, $values)->update($values);
        }

        return $updated;
    }

    public function values(Recei_Add $type): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $values[$field] = $type->{$field};
        }
        $values['letter_1_days'] = 0;

        return $values;
    }

    public function mismatches(string $typeName, array $values): Builder
    {
        return $this->openReceipts($typeName)->where(function (Builder $query) use ($values) {
            foreach ($values as $field => $value) {
                $query->orWhereRaw("NOT (`{$field}` <=> ?)", [$value]);
            }
        });
    }
}
