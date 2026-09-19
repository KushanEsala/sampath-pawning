<?php

namespace App\Services;

use App\Models\TOpeningPawnDetails;
use App\Models\TPawnDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ReceiptArticleStatus
{
    public static function sync(Model $receipt, string $type = 'Pawn'): void
    {
        $model = $type === 'Opening_Pawn' ? TOpeningPawnDetails::class : TPawnDetails::class;
        $values = ['IsRedeemed'=>(int) $receipt->IsRedeemed];
        // The original dump has no isForfeit column on t_pawn_details.
        if (Schema::hasColumn((new $model())->getTable(), 'isForfeit')) {
            $values['isForfeit'] = (int) $receipt->isForfeit;
        }
        $model::where('BC', $receipt->BC)->where('Receipt_Number', $receipt->Receipt_Number)->update($values);
    }
}
