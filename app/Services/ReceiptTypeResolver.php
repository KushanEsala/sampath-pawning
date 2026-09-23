<?php

namespace App\Services;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ReceiptTypeResolver
{
    /**
     * Resolve a Recei_Add configuration instance for a specific receipt.
     * Prioritizes the snapshot fields on the receipt itself (guaranteeing 0% retroactive changes).
     * If any fields are missing, falls back to the historical Recei_Add record effective on the receipt's pawn date.
     */
    public function resolveForReceipt(TPawnSum|Model|array $receipt): Recei_Add
    {
        $receiptObj = is_array($receipt) ? (object) $receipt : $receipt;
        $receiptName = $receiptObj->Receipt_Type ?? $receiptObj->receiptname ?? null;
        $pawnDate = $receiptObj->Pawn_Date ?? $receiptObj->pawn_date ?? null;

        // Check if receipt already has rate snapshot columns populated
        $hasSnapshot = isset($receiptObj->rate1) && $receiptObj->rate1 !== null;

        if ($hasSnapshot) {
            $recei = new Recei_Add();
            $recei->exists = true; // behaves as loaded model
            $recei->receiptname = $receiptName;
            $recei->rate1 = $receiptObj->rate1;
            $recei->period1 = $receiptObj->period1 ?? 0;
            $recei->rate2 = $receiptObj->rate2 ?? 0;
            $recei->period2 = $receiptObj->period2 ?? 0;
            $recei->rate3 = $receiptObj->rate3 ?? 0;
            $recei->period3 = $receiptObj->period3 ?? 0;
            $recei->validPeriod = $receiptObj->validPeriod ?? $receiptObj->Valid_Period ?? 0;
            $recei->service_charge = $receiptObj->service_charge ?? 0;
            $recei->Postage_charge = $receiptObj->Postage_charge ?? 0;
            $recei->s_charge_less = $receiptObj->s_charge_less ?? 0;
            $recei->s_charge_greater = $receiptObj->s_charge_greater ?? 0;
            $recei->documentCharges = $receiptObj->documentCharges ?? 0;
            $recei->stampduty = $receiptObj->stampduty ?? 0;
            $recei->pawn_amount = $receiptObj->Pawn_Advance_Amount ?? $receiptObj->pawn_amount ?? 0;
            $recei->letter_1_days = $receiptObj->letter_1_days ?? 21;
            $recei->letter_2_days = $receiptObj->letter_2_days ?? 21;
            $recei->letter_3_days = $receiptObj->letter_3_days ?? 21;
            $recei->forfeit_reminder_days = $receiptObj->forfeit_reminder_days ?? 21;
            $recei->effective_from = $pawnDate;
            $recei->is_active = 1;

            return $recei;
        }

        // Fallback: resolve from historical recei__adds by pawn date
        $historical = $this->resolveByDate((string) $receiptName, $pawnDate);
        if ($historical) {
            return $historical;
        }

        // Ultimate fallback: return a default empty model
        $fallback = new Recei_Add();
        $fallback->receiptname = $receiptName;
        return $fallback;
    }

    /** Keep the rate snapshot saved for this receipt's current pawn/repawn cycle. */
    public function resolveForCurrentCycle(TPawnSum|Model|array $receipt): Recei_Add
    {
        return $this->resolveForReceipt($receipt);
    }

    /**
     * Return an unsaved receipt copy with its current-cycle saved configuration.
     * The stored receipt row is never modified here.
     */
    public function receiptForCalculation(TPawnSum $receipt): TPawnSum
    {
        $configuration = $this->resolveForCurrentCycle($receipt);
        $copy = clone $receipt;

        foreach ([
            'rate1', 'period1', 'rate2', 'period2', 'rate3', 'period3',
            'validPeriod', 'service_charge', 'Postage_charge',
            's_charge_less', 's_charge_greater', 'documentCharges', 'stampduty',
            'letter_1_days', 'letter_2_days', 'letter_3_days',
            'forfeit_reminder_days',
        ] as $field) {
            if ($configuration->{$field} !== null) {
                $copy->setAttribute($field, $configuration->{$field});
            }
        }

        // Negative carried interest is invalid and was produced by the legacy
        // part-payment formula. Also clear cumulative paid-interest values when
        // the latest transaction started the receipt's current cycle.
        $copy->setAttribute('BalanceInterest', max(0, (float) ($copy->BalanceInterest ?? 0)));
        $this->normaliseCycleBalances($copy);

        return $copy;
    }

    private function normaliseCycleBalances(TPawnSum $receipt): void
    {
        if (empty($receipt->Receipt_Number) || empty($receipt->BC)) {
            return;
        }

        $cycleStart = $receipt->RePawning_date ?: $receipt->Pawn_Date;
        if (!$cycleStart) {
            return;
        }

        $latest = DB::table('t_pawn_trans')
            ->where('code', $receipt->Receipt_Number)
            ->where('BC', $receipt->BC)
            ->whereIn('trans_type', ['PART_PAYMENT', 'REPAWNING'])
            ->whereDate('dDate', '<=', Carbon::parse($cycleStart)->toDateString())
            ->orderByDesc('dDate')
            ->orderByDesc('id')
            ->first(['trans_type', 'dDate', 'interest_Balance']);

        if (!$latest) {
            return;
        }

        $transactionDate = Carbon::parse($latest->dDate)->startOfDay();
        $startDate = Carbon::parse($cycleStart)->startOfDay();
        $startsCurrentCycle = $transactionDate->equalTo($startDate)
            || (strtoupper((string) $latest->trans_type) === 'PART_PAYMENT'
                && $transactionDate->copy()->addDay()->equalTo($startDate));

        if (!$startsCurrentCycle) {
            return;
        }

        $receipt->setAttribute('interest_Paid', 0);
        $receipt->setAttribute(
            'BalanceInterest',
            strtoupper((string) $latest->trans_type) === 'PART_PAYMENT'
                ? max(0, (float) ($latest->interest_Balance ?? 0))
                : 0
        );
    }

    /**
     * In-memory cache for resolved Recei_Add configurations by name and date.
     */
    private array $dateCache = [];

    /**
     * Resolve a Recei_Add configuration instance for a receipt name as of a specific date.
     * Useful for new pawn creation and repawning loans.
     */
    public function resolveByDate(string $receiptName, Carbon|string|null $date = null): ?Recei_Add
    {
        $parsedDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        $cacheKey = "{$receiptName}|{$parsedDate}";
        if (isset($this->dateCache[$cacheKey])) {
            return $this->dateCache[$cacheKey];
        }

        // 1. Try to find record where effective_from <= date and (effective_to is null or effective_to >= date)
        $match = Recei_Add::where('receiptname', $receiptName)
            ->effectiveOn($parsedDate)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        if ($match) {
            return $this->dateCache[$cacheKey] = $match;
        }

        // 2. If no date match found (e.g. date is before the earliest effective_from),
        // get the oldest available record for this receipt name
        $earliest = Recei_Add::where('receiptname', $receiptName)
            ->orderBy('effective_from')
            ->orderBy('id')
            ->first();

        if ($earliest) {
            return $this->dateCache[$cacheKey] = $earliest;
        }

        // 3. Fallback to any record matching receiptname
        return $this->dateCache[$cacheKey] = Recei_Add::where('receiptname', $receiptName)->first();
    }

    /**
     * Return a collection containing the resolved Recei_Add for the given receipt.
     * This is designed as a drop-in replacement for `Recei_Add::where('receiptname', ...)->get()`.
     */
    public function resolveForReceiptCollection(TPawnSum|Model|array|null $receipt, ?string $receiptNameFallback = null): Collection
    {
        if ($receipt) {
            return collect([$this->resolveForReceipt($receipt)]);
        }

        if ($receiptNameFallback) {
            $byDate = $this->resolveByDate($receiptNameFallback);
            return $byDate ? collect([$byDate]) : collect();
        }

        return collect();
    }

    /**
     * Get all currently active receipt types (for dropdowns / selection).
     */
    public function getActiveTypes(): Collection
    {
        return Recei_Add::active()
            ->orderBy('receiptname')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get()
            ->unique('receiptname')
            ->values();
    }

    /**
     * Resolve the receipt type that applies to a newly repawned principal.
     * These are the same amount bands used when the receipt snapshot is
     * refreshed after repawning.
     */
    public function resolveForRepawnAmount(float $amount, Carbon|string|null $date = null, bool $silver = false): ?Recei_Add
    {
        if ($silver) {
            return $this->resolveByDate('SILVER', $date);
        }

        $parsedDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return Recei_Add::query()
            ->where('validPeriod', '>', 0)
            ->whereRaw('UPPER(receiptname) <> ?', ['SILVER'])
            ->effectiveOn($parsedDate)
            ->where(function ($query) use ($amount) {
                if ($amount >= 100000) {
                    $query->where('pawn_amount', '>=', 100000);
                } elseif ($amount >= 50000) {
                    $query->whereBetween('pawn_amount', [50000, 99999]);
                } else {
                    $query->where('pawn_amount', '<', 50000);
                }
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }
}
