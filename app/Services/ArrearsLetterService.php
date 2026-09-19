<?php

namespace App\Services;

use App\Models\ArrearsLetterEvent;
use App\Models\TPawnSum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ArrearsLetterService
{
    public function __construct(
        private ReceiptFinancialCalculator $calculator,
        private ReceiptLifecycleService $lifecycle
    ) {}

    public function dueDate(TPawnSum $receipt, int $letterNo): Carbon
    {
        return (new ReceiptPenaltySchedule())->letterDueDate($receipt, $letterNo);
    }

    public function isEligible(TPawnSum $receipt, int $letterNo, ?Carbon $today = null): bool
    {
        $today = ($today ?: now())->copy()->startOfDay();

        if ($receipt->IsRedeemed || $receipt->isForfeit || $today->lt($this->dueDate($receipt, $letterNo))) {
            return false;
        }

        return match ($letterNo) {
            1 => !(bool) $receipt->is_letter_1,
            2 => (bool) $receipt->is_letter_1 && !(bool) $receipt->is_letter_2,
            3 => (bool) $receipt->is_letter_2 && !(bool) $receipt->is_letter_3,
            default => false,
        };
    }

    public function issue(int $pawnSumId, string $branchCode, int $letterNo): ArrearsLetterEvent
    {
        if (!Schema::hasTable('arrears_letter_events')) {
            throw new RuntimeException('Run database/manual/001_create_arrears_letter_events.sql manually first.');
        }

        return DB::transaction(function () use ($pawnSumId, $branchCode, $letterNo) {
            $receipt = TPawnSum::where('id', $pawnSumId)
                ->where('BC', $branchCode)
                ->lockForUpdate()
                ->firstOrFail();

            $cycleNo = $this->lifecycle->cycleNumber($receipt);
            $existing = ArrearsLetterEvent::where('pawn_sum_id', $receipt->id)
                ->where('cycle_no', $cycleNo)
                ->where('letter_no', $letterNo)
                ->first();

            if ($existing) {
                return $existing;
            }

            if (!$this->isEligible($receipt, $letterNo)) {
                throw new RuntimeException('This receipt is not eligible for the requested letter yet.');
            }

            $postage = $this->calculator->postageCharge($receipt);
            $service = $letterNo === 1 ? $this->calculator->calculate($receipt)['service_charge'] : 0;
            $event = ArrearsLetterEvent::create([
                'pawn_sum_id' => $receipt->id,
                'BC' => $receipt->BC,
                'receipt_number' => $receipt->Receipt_Number,
                'cycle_no' => $cycleNo,
                'letter_no' => $letterNo,
                'due_date' => $this->dueDate($receipt, $letterNo),
                'issued_at' => now(),
                'postage_charge' => $postage,
                'service_charge' => $service,
                'issued_by' => optional(auth()->user())->username ?? 'system',
            ]);

            $receipt->update([
                "is_letter_{$letterNo}" => 1,
                "letter_{$letterNo}_date" => now()->toDateString(),
                ['letter_pay_one', 'letter_pay_two', 'letter_pay_three'][$letterNo - 1] => $postage,
            ]);

            $this->lifecycle->record(
                $receipt->fresh(),
                "LETTER_{$letterNo}_ISSUED",
                $postage,
                "Letter {$letterNo} issued.",
                ['due_date' => $event->due_date->toDateString(), 'cycle_no' => $cycleNo]
            );

            return $event;
        });
    }
}
