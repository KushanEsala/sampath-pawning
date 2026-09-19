<?php

namespace App\Http\Controllers;

use App\Models\ForfeitReminderPromise;
use App\Models\TPawnSum;
use App\Models\Customer;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptPenaltySchedule;
use App\Services\ForfeitReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ForfeitReminderController extends Controller
{
    public function index(Request $request, ReceiptFinancialCalculator $calculator, ReceiptPenaltySchedule $schedule, ForfeitReminderService $reminders)
    {
        $request->validate(['receipt_number' => 'nullable|string|max:80', 'per_page' => 'nullable|in:10,25,50,100']);
        $query = TPawnSum::where('BC', auth()->user()->BC)->where('IsRedeemed', 0)->where('isForfeit', 0);
        $schedule->dueReminders($query);
        if (Schema::hasColumn('t_pawn_sums', 'forfeit_queued_at')) $query->whereNull('forfeit_queued_at');
        if ($request->filled('receipt_number')) $query->where('Receipt_Number', $request->receipt_number);
        $receipts = $query->orderBy('letter_3_date')->orderBy('id')
            ->paginate((int) $request->input('per_page', 25))->withQueryString();
        $receipts->getCollection()->each(function (TPawnSum $receipt) use ($calculator, $schedule, $reminders) {
            $promise = $reminders->currentPromise($receipt);
            $receipt->setAttribute('current_promise', $promise);
            $receipt->setAttribute('can_queue_forfeit', $reminders->canQueue($receipt, $promise));
            $receipt->setAttribute('reminder_due_date', $schedule->reminderDueDate($receipt)->toDateString());
            $receipt->setAttribute('financial_breakdown', $calculator->calculate($receipt));
            $customer = Customer::where('NIC', $receipt->Customer_NIC)->where('BC', $receipt->BC)->first();
            $receipt->setAttribute('current_customer_name', $customer?->Name ?: $receipt->Customer_Name);
            $receipt->setAttribute('current_customer_phone', $customer?->Contact_1 ?: $receipt->Customer_Phone);
        });
        return view('forfeitReminderList', compact('receipts'));
    }

    public function queue(Request $request, ForfeitReminderService $reminders)
    {
        $values = $request->validate(['pawn_sum_id' => 'required|integer', 'confirm_forfeit' => 'accepted']);
        $reminders->queue((int) $values['pawn_sum_id'], auth()->user()->BC);
        return back()->with('done', 'Receipt moved to Forfeit List for final processing.');
    }

    public function storePromise(Request $request, ReceiptLifecycleService $lifecycle, ReceiptPenaltySchedule $schedule)
    {
        if (!Schema::hasTable('forfeit_reminder_promises')) {
            return back()->with('error', 'Promise setup is pending. Please contact your administrator.');
        }
        $validated = $request->validate([
            'pawn_sum_id' => ['required', 'integer'],
            'promise_date' => ['required', 'date', 'after_or_equal:today'],
            'remark' => ['required', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($validated, $lifecycle, $schedule) {
            $receipt = TPawnSum::where('id', $validated['pawn_sum_id'])->where('BC', auth()->user()->BC)
                ->where('IsRedeemed', 0)->where('isForfeit', 0)->lockForUpdate()->firstOrFail();
            if (!$schedule->reminderIsDue($receipt) || $receipt->forfeit_queued_at) {
                throw ValidationException::withMessages(['promise_date' => 'This receipt is not in the Forfeit Reminder List.']);
            }
            $cycle = $lifecycle->cycleNumber($receipt);
            $current = ForfeitReminderPromise::where('pawn_sum_id', $receipt->id)
                ->where('cycle_no', $cycle)->latest('id')->lockForUpdate()->first();
            if ($current && today()->gte($current->promise_date)) {
                throw ValidationException::withMessages(['promise_date' => 'The current promise date has arrived and can no longer be changed.']);
            }
            if ($current) $current->update(['status' => 'EXTENDED', 'updated_by' => auth()->user()->username]);
            $promise = ForfeitReminderPromise::create([
                'pawn_sum_id' => $receipt->id, 'BC' => $receipt->BC,
                'receipt_number' => $receipt->Receipt_Number, 'cycle_no' => $cycle,
                'promise_date' => $validated['promise_date'], 'remark' => $validated['remark'],
                'status' => 'PENDING', 'previous_promise_id' => $current?->id,
                'created_by' => auth()->user()->username, 'updated_by' => auth()->user()->username,
            ]);
            $lifecycle->record($receipt, $current ? 'PROMISE_EXTENDED' : 'PROMISE_ENTERED', null, $validated['remark'], [
                'promise_date' => $promise->promise_date->toDateString(),
            ]);
        });
        return back()->with('done', 'Customer promise saved successfully.');
    }
}
