<?php

namespace App\Services;

use App\Models\TPawnSum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ReceiptPenaltySchedule
{
    public const FIELDS = ['letter_1_days', 'letter_2_days', 'letter_3_days', 'forfeit_reminder_days'];

    public function letterDueDate(TPawnSum $receipt, int $letter): Carbon
    {
        if ($letter < 1 || $letter > 3) throw new \InvalidArgumentException('Invalid letter number.');
        $days = 0;
        for ($number = 1; $number <= $letter; $number++) {
            $days += (int) ($receipt->{'letter_'.$number.'_days'} ?? 21);
        }
        return Carbon::parse($receipt->Final_date)->startOfDay()->addDays($days);
    }

    public function reminderDueDate(TPawnSum $receipt): ?Carbon
    {
        if (!$receipt->is_letter_3 || !$receipt->letter_3_date) return null;
        return Carbon::parse($receipt->letter_3_date)->startOfDay()
            ->addDays((int) ($receipt->forfeit_reminder_days ?? 21));
    }

    public function reminderIsDue(TPawnSum $receipt, ?Carbon $date = null): bool
    {
        $due = $this->reminderDueDate($receipt);
        return !$receipt->IsRedeemed && !$receipt->isForfeit && $due
            && $due->lte(($date ?? today())->copy()->startOfDay());
    }

    public function dueReminders(Builder $query): Builder
    {
        $days = Schema::hasColumn('t_pawn_sums', 'forfeit_reminder_days')
            ? 'COALESCE(forfeit_reminder_days, 21)' : '21';
        return $query->where('is_letter_3', 1)->whereNotNull('letter_3_date')
            ->whereRaw("DATE_ADD(DATE(letter_3_date), INTERVAL {$days} DAY) <= ?", [today()->toDateString()]);
    }

    public function letterDueSql(int $letter): string
    {
        if ($letter < 1 || $letter > 3) throw new \InvalidArgumentException('Invalid letter number.');
        $fields = [];
        $configured = Schema::hasColumn('t_pawn_sums', 'letter_1_days');
        for ($number = 1; $number <= $letter; $number++) {
            $fields[] = $configured ? "COALESCE(letter_{$number}_days, 21)" : '21';
        }
        return 'DATE_ADD(DATE(Final_date), INTERVAL ('.implode(' + ', $fields).') DAY)';
    }
}
