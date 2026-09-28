<?php

namespace App\Services;

use App\Models\TPawnSum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
        $due = $this->expiryDate($receipt)->addDays($days);
        if ($letter > 1 && $receipt->{'letter_'.($letter - 1).'_date'}) {
            $afterPrinting = Carbon::parse($receipt->{'letter_'.($letter - 1).'_date'})->startOfDay()
                ->addDays((int) ($receipt->{'letter_'.$letter.'_days'} ?? 21));
            if ($afterPrinting->gt($due)) $due = $afterPrinting;
        }
        return $due;
    }

    public function expiryDate(TPawnSum $receipt): Carbon
    {
        if (!$this->isSilver($receipt)) {
            return Carbon::parse($receipt->Final_date)->startOfDay();
        }

        $cycleStart = $receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date;
        if ($receipt->To_Date && (!$cycleStart || Carbon::parse($receipt->To_Date)->startOfDay()
            ->gt(Carbon::parse($cycleStart)->startOfDay()))) {
            return Carbon::parse($receipt->To_Date)->startOfDay();
        }
        if (!$cycleStart) return Carbon::parse($receipt->Final_date)->startOfDay();

        // Older Silver part payments/repawns did not refresh To_Date. Recover
        // the expiry from the actual latest transaction without rewriting it.
        $latestCycleDate = null;
        if ($receipt->exists && $receipt->BC && $receipt->Receipt_Number) {
            $latestCycleDate = DB::table('t_pawn_trans')
                ->whereRaw('BINARY BC = BINARY ?', [$receipt->BC])
                ->whereRaw('BINARY code = BINARY ?', [$receipt->Receipt_Number])
                ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
                ->orderByDesc('dDate')->orderByDesc('id')->value('dDate');
        }
        $days = (int) ($receipt->validPeriod ?: $receipt->period3);
        if ($days <= 0) {
            $historicalType = app(ReceiptTypeResolver::class)
                ->resolveByDate('SILVER', $receipt->Receipt_Date ?: $receipt->Pawn_Date);
            $days = SilverInterest::validDays($historicalType);
        }
        return Carbon::parse($latestCycleDate ?: $cycleStart)->startOfDay()->addDays($days);
    }

    private function isSilver(TPawnSum $receipt): bool
    {
        return strtoupper(trim((string) $receipt->receiptname)) === 'SILVER'
            || strtoupper(trim((string) $receipt->Receipt_Type)) === 'SILVER';
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
        $scheduled = 'DATE_ADD('.$this->expirySql().', INTERVAL ('.implode(' + ', $fields).') DAY)';
        if ($letter === 1) return $scheduled;

        $interval = $configured ? "COALESCE(letter_{$letter}_days, 21)" : '21';
        $previous = "letter_".($letter - 1)."_date";
        return "GREATEST({$scheduled}, COALESCE(DATE_ADD(DATE({$previous}), INTERVAL {$interval} DAY), {$scheduled}))";
    }

    public function expirySql(): string
    {
        $cycle = 'DATE(COALESCE(RePawning_date, Pawn_Date, Receipt_Date))';
        $latest = '(SELECT DATE(t.dDate) FROM t_pawn_trans t WHERE BINARY t.BC = BINARY t_pawn_sums.BC '
            .'AND BINARY t.code = BINARY t_pawn_sums.Receipt_Number '
            ."AND t.trans_type IN ('PART_PAYMENT', 'REPAWNING') "
            .'ORDER BY t.dDate DESC, t.id DESC LIMIT 1)';
        $historicalDays = "(SELECT COALESCE(NULLIF(a.validPeriod, 0), NULLIF(a.period3, 0)) FROM recei__adds a "
            ."WHERE UPPER(TRIM(a.receiptname)) = 'SILVER' "
            .'AND (a.effective_from IS NULL OR DATE(a.effective_from) <= DATE(COALESCE(Receipt_Date, Pawn_Date))) '
            .'ORDER BY a.effective_from DESC, a.id DESC LIMIT 1)';
        $days = 'COALESCE(NULLIF(validPeriod, 0), NULLIF(period3, 0), '.$historicalDays.')';
        $silverExpiry = 'CASE WHEN To_Date IS NOT NULL AND ('.$cycle.' IS NULL OR DATE(To_Date) > '.$cycle.') '
            .'THEN DATE(To_Date) ELSE DATE_ADD(COALESCE('.$latest.', '.$cycle.'), INTERVAL '.$days.' DAY) END';
        return "(CASE WHEN UPPER(TRIM(COALESCE(receiptname, ''))) = 'SILVER' "
            ."OR UPPER(TRIM(COALESCE(Receipt_Type, ''))) = 'SILVER' "
            .'THEN '.$silverExpiry.' ELSE DATE(Final_date) END)';
    }

    public function reminderDueSql(): string
    {
        $days = Schema::hasColumn('t_pawn_sums', 'forfeit_reminder_days')
            ? 'COALESCE(forfeit_reminder_days, 21)' : '21';
        return "DATE_ADD(DATE(letter_3_date), INTERVAL {$days} DAY)";
    }
}
