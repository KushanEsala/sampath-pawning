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

    public function letterDueDate(TPawnSum $receipt, int $letter): ?Carbon
    {
        if ($letter < 1 || $letter > 3) throw new \InvalidArgumentException('Invalid letter number.');
        // Letter 1 is printable on expiry. Each later interval starts only
        // when the preceding letter was actually printed, not on its due date.
        if ($letter === 1) return $this->expiryDate($receipt);

        $printedAt = $receipt->{'letter_'.($letter - 1).'_date'};
        if (!$printedAt) return null;

        return Carbon::parse($printedAt)->startOfDay()
            ->addDays((int) ($receipt->{'letter_'.$letter.'_days'} ?? 21));
    }

    public function expiryDate(TPawnSum $receipt): Carbon
    {
        // The Late Letters list selects its effective expiry with the same SQL
        // expression used for filtering and sorting. Reuse that date rather
        // than re-querying transactions for every Silver receipt on a page.
        if ($receipt->getAttribute('letter_effective_expiry_date')) {
            return Carbon::parse($receipt->letter_effective_expiry_date)->startOfDay();
        }

        if (!$this->isSilver($receipt)) {
            return Carbon::parse($receipt->Final_date)->startOfDay();
        }

        $cycleStart = $receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date;
        // A payment/repawn starts a new Silver expiry period even when an old
        // To_Date is still in the future. Do not trust that stale stored date.
        $latestCycleDate = null;
        if ($receipt->exists && $receipt->BC && $receipt->Receipt_Number) {
            $latestCycleDate = DB::table('t_pawn_trans')
                ->whereRaw('BINARY BC = BINARY ?', [$receipt->BC])
                ->whereRaw('BINARY code = BINARY ?', [$receipt->Receipt_Number])
                ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
                ->orderByDesc('dDate')->orderByDesc('id')->value('dDate');
        }
        if (!$latestCycleDate && $receipt->To_Date && (!$cycleStart || Carbon::parse($receipt->To_Date)->startOfDay()
            ->gt(Carbon::parse($cycleStart)->startOfDay()))) {
            return Carbon::parse($receipt->To_Date)->startOfDay();
        }
        if (!$latestCycleDate && !$cycleStart) return Carbon::parse($receipt->Final_date)->startOfDay();
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
        $asOf = ($date ?? today())->copy()->startOfDay();
        $cycleStart = $receipt->RePawning_date ?: $receipt->Pawn_Date ?: $receipt->Receipt_Date;

        if ($receipt->IsRedeemed || $receipt->isForfeit || !$due
            || ($cycleStart && Carbon::parse($receipt->letter_3_date)->startOfDay()
                ->lt(Carbon::parse($cycleStart)->startOfDay()))
            || $this->expiryDate($receipt)->gt($asOf)
            || $due->gt($asOf)) {
            return false;
        }

        return !$receipt->exists || !DB::table('t_pawn_trans')
            ->whereRaw('BINARY BC = BINARY ?', [$receipt->BC])
            ->whereRaw('BINARY code = BINARY ?', [$receipt->Receipt_Number])
            ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
            ->whereDate('dDate', '>', Carbon::parse($receipt->letter_3_date)->toDateString())
            ->exists();
    }

    public function dueReminders(Builder $query): Builder
    {
        return $query->where('is_letter_3', 1)->whereNotNull('letter_3_date')
            ->whereRaw($this->reminderDueSql().' <= ?', [today()->toDateString()])
            ->whereRaw($this->expirySql().' <= ?', [today()->toDateString()])
            ->whereRaw('(COALESCE(RePawning_date, Pawn_Date, Receipt_Date) IS NULL '
                .'OR DATE(letter_3_date) >= DATE(COALESCE(RePawning_date, Pawn_Date, Receipt_Date)))')
            ->whereRaw('NOT EXISTS (SELECT 1 FROM t_pawn_trans t WHERE BINARY t.BC = BINARY t_pawn_sums.BC '
                .'AND BINARY t.code = BINARY t_pawn_sums.Receipt_Number '
                ."AND t.trans_type IN ('PART_PAYMENT', 'REPAWNING') "
                .'AND DATE(t.dDate) > DATE(t_pawn_sums.letter_3_date))');
    }

    public function letterDueSql(int $letter, ?string $latestCycleDateSql = null): string
    {
        if ($letter < 1 || $letter > 3) throw new \InvalidArgumentException('Invalid letter number.');
        if ($letter === 1) return $this->expirySql($latestCycleDateSql);
        $configured = Schema::hasColumn('t_pawn_sums', 'letter_2_days');
        $days = $configured ? "COALESCE(letter_{$letter}_days, 21)" : '21';
        return "DATE_ADD(DATE(letter_".($letter - 1)."_date), INTERVAL {$days} DAY)";
    }

    public function expirySql(?string $latestCycleDateSql = null): string
    {
        $cycle = 'DATE(COALESCE(RePawning_date, Pawn_Date, Receipt_Date))';
        $latest = $latestCycleDateSql
            ? 'DATE('.$latestCycleDateSql.')'
            : '(SELECT DATE(t.dDate) FROM t_pawn_trans t WHERE BINARY t.BC = BINARY t_pawn_sums.BC '
                .'AND BINARY t.code = BINARY t_pawn_sums.Receipt_Number '
                ."AND t.trans_type IN ('PART_PAYMENT', 'REPAWNING') "
                .'ORDER BY t.dDate DESC, t.id DESC LIMIT 1)';
        $historicalDays = "(SELECT COALESCE(NULLIF(a.validPeriod, 0), NULLIF(a.period3, 0)) FROM recei__adds a "
            ."WHERE UPPER(TRIM(a.receiptname)) = 'SILVER' "
            .'AND (a.effective_from IS NULL OR DATE(a.effective_from) <= DATE(COALESCE(Receipt_Date, Pawn_Date))) '
            .'ORDER BY a.effective_from DESC, a.id DESC LIMIT 1)';
        $days = 'COALESCE(NULLIF(validPeriod, 0), NULLIF(period3, 0), '.$historicalDays.')';
        $silverExpiry = 'COALESCE(DATE_ADD('.$latest.', INTERVAL '.$days.' DAY), '
            .'CASE WHEN To_Date IS NOT NULL AND ('.$cycle.' IS NULL OR DATE(To_Date) > '.$cycle.') '
            .'THEN DATE(To_Date) ELSE DATE_ADD('.$cycle.', INTERVAL '.$days.' DAY) END)';
        return "(CASE WHEN UPPER(TRIM(COALESCE(receiptname, ''))) = 'SILVER' "
            ."OR UPPER(TRIM(COALESCE(Receipt_Type, ''))) = 'SILVER' "
            .'THEN '.$silverExpiry.' ELSE DATE(Final_date) END)';
    }

    public function reminderDueSql(?string $latestCycleDateSql = null): string
    {
        $days = Schema::hasColumn('t_pawn_sums', 'forfeit_reminder_days')
            ? 'COALESCE(forfeit_reminder_days, 21)' : '21';
        return "DATE_ADD(DATE(letter_3_date), INTERVAL {$days} DAY)";
    }
}
