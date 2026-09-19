<?php

namespace App\Services;

use App\Models\ForfeitReminderPromise;
use App\Models\TPawnSum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ForfeitReminderService
{
    public function __construct(private ReceiptPenaltySchedule $schedule, private ReceiptLifecycleService $lifecycle) {}

    public function currentPromise(TPawnSum $receipt): ?ForfeitReminderPromise
    {
        if (!Schema::hasTable('forfeit_reminder_promises')) return null;
        return ForfeitReminderPromise::where('pawn_sum_id', $receipt->id)
            ->where('cycle_no', $this->lifecycle->cycleNumber($receipt))->latest('id')->first();
    }

    public function canQueue(TPawnSum $receipt, ?ForfeitReminderPromise $promise): bool
    {
        return $this->schedule->reminderIsDue($receipt)
            && !$receipt->forfeit_queued_at
            && (!$promise || $promise->status === 'BROKEN'
                || (in_array($promise->status, ['PENDING', 'EXTENDED'], true) && $promise->promise_date->lt(today())));
    }

    public function queue(int $id, string $branch): void
    {
        if (!Schema::hasColumn('t_pawn_sums', 'forfeit_queued_at')) {
            throw ValidationException::withMessages(['forfeit' => 'Forfeit-list setup is pending. Please ask your administrator to apply manual SQL 005.']);
        }
        DB::transaction(function () use ($id, $branch) {
            $receipt = TPawnSum::where('id', $id)->where('BC', $branch)->lockForUpdate()->firstOrFail();
            if ($receipt->forfeit_queued_at && !$receipt->IsRedeemed && !$receipt->isForfeit) return;
            $promise = $this->currentPromise($receipt);
            if (!$this->canQueue($receipt, $promise)) {
                throw ValidationException::withMessages(['forfeit' => 'The reminder is not due, this receipt is closed, or its payment promise is still valid.']);
            }
            $receipt->update(['forfeit_queued_at' => now()]);
            if ($promise) $promise->update(['status' => 'BROKEN', 'updated_by' => auth()->user()->username]);
            $this->lifecycle->record($receipt, 'FORFEIT_QUEUED', null, 'Operator reviewed the unpaid receipt and moved it to Forfeit List.');
        });
    }
}
