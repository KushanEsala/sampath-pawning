<?php

namespace App\Services;

use App\Models\ForfeitReminderPromise;
use App\Models\ReceiptLifecycleEvent;
use App\Models\TPawnSum;
use Illuminate\Support\Facades\Schema;

class ReceiptLifecycleService
{
    public function cycleNumber(TPawnSum $receipt): int
    {
        if (!Schema::hasTable('receipt_lifecycle_events')) {
            return 1;
        }

        return ReceiptLifecycleEvent::where('pawn_sum_id', $receipt->id)
            ->where('event_type', 'REACTIVATED')
            ->count() + 1;
    }

    public function record(TPawnSum $receipt, string $type, ?float $amount = null, ?string $description = null, array $data = []): void
    {
        if (!Schema::hasTable('receipt_lifecycle_events')) {
            return;
        }

        ReceiptLifecycleEvent::create([
            'pawn_sum_id' => $receipt->id,
            'BC' => $receipt->BC,
            'receipt_number' => $receipt->Receipt_Number,
            'event_type' => $type,
            'event_date' => now(),
            'amount' => $amount,
            'description' => $description,
            'event_data' => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            'created_by' => optional(auth()->user())->username ?? 'system',
        ]);
    }

    public function reactivate(TPawnSum $receipt, float $amount, string $description): void
    {
        if (Schema::hasColumn('t_pawn_sums', 'forfeit_queued_at')) {
            $receipt->forfeit_queued_at = null;
        }
        $receipt->update([
            'is_letter_1' => 0,
            'is_letter_2' => 0,
            'is_letter_3' => 0,
            'letter_1_date' => null,
            'letter_2_date' => null,
            'letter_3_date' => null,
            'letter_pay_one' => 0,
            'letter_pay_two' => 0,
            'letter_pay_three' => 0,
        ]);

        if (Schema::hasTable('forfeit_reminder_promises')) {
            ForfeitReminderPromise::where('pawn_sum_id', $receipt->id)
                ->whereIn('status', ['PENDING', 'EXTENDED'])
                ->update([
                    'status' => 'KEPT',
                    'updated_by' => optional(auth()->user())->username ?? 'system',
                    'updated_at' => now(),
                ]);
        }

        $this->record($receipt->fresh(), 'REACTIVATED', $amount, $description);
    }

    public function closeForRedemption(TPawnSum $receipt, float $amount): void
    {
        if (Schema::hasColumn('t_pawn_sums', 'forfeit_queued_at')) {
            $receipt->update(['forfeit_queued_at' => null]);
        }
        if (Schema::hasTable('forfeit_reminder_promises')) {
            ForfeitReminderPromise::where('pawn_sum_id', $receipt->id)
                ->whereIn('status', ['PENDING', 'EXTENDED'])
                ->update([
                    'status' => 'KEPT',
                    'updated_by' => optional(auth()->user())->username ?? 'system',
                    'updated_at' => now(),
                ]);
        }

        $this->record($receipt, 'REDEEMED', $amount, 'Receipt fully redeemed.');
    }
}
