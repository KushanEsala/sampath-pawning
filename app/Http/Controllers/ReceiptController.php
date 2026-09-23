<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recei_Add;
use App\Services\ReceiptPenaltySchedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function index(Request $request){
        $receipt = Recei_Add::active()->orderBy('receiptname')->orderByDesc('effective_from')->get();
        return view('receipt', compact('receipt'))->with('Recei_Add', $receipt);
    }

    public function history(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:80']);
        $versions = Recei_Add::query()->when($request->filled('name'), fn ($query) => $query->where('receiptname', $request->name))
            ->orderBy('receiptname')->orderByDesc('effective_from')->orderByDesc('id')->get();

        return view('receipt_type_history', ['versions' => $versions, 'typeName' => $request->name ?: 'All types']);
    }

    public function index_forfeit_receipt(){
        return view('ForfeitReceipt');
    }

    // Create receipt using Ajax
    public function create(Request $request){
        $intervals = $this->validatedIntervals($request);
        $request->validate([
            'receiptname' => 'required|max:80',
            'effective_from' => 'nullable|date|before_or_equal:today',
            'rate1' => 'required|numeric',
            'rate2' => 'required|numeric',
            'rate3' => 'nullable|numeric',
            'period1' => 'required|numeric',
            'period2' => 'required|numeric',
            'period3' => 'required|numeric',
            'valid' => 'required|numeric',
            's_char_less' => 'required|numeric',
            's_char_grea' => 'required|numeric',
            'Postage_charge' => 'required|numeric',
            'service_charge' => 'required|numeric',
        ], [
            'effective_from.before_or_equal' => 'Future scheduling is not supported. Make this change on its effective date.',
            'receiptname.required' => 'Receipt type name is required',
            'rate1.required' => 'Rate 1 is required',
            'rate1.numeric' => 'Rate 1 must be a number',
            'rate2.required' => 'Rate 2 is required',
            'rate2.numeric' => 'Rate 2 must be a number',
            'rate3.numeric' => 'Rate 3 must be a number',
            'period1.required' => 'Period 1 is required',
            'period1.numeric' => 'Period 1 must be a number',
            'period2.required' => 'Period 2 is required',
            'period2.numeric' => 'Period 2 must be a number',
            'period3.required' => 'Period 3 is required',
            'period3.numeric' => 'Period 3 must be a number',
            'valid.required' => 'Valid period is required',
            'valid.numeric' => 'Valid period must be a number',
            's_char_less.required' => 'Service charge (< 25000) is required',
            's_char_less.numeric' => 'Service charge (< 25000) must be a number',
            's_char_grea.required' => 'Service charge (> 25000) is required',
            's_char_grea.numeric' => 'Service charge (> 25000) must be a number',
            'Postage_charge.required' => 'Postage charge is required',
            'Postage_charge.numeric' => 'Postage charge must be a number',
            'service_charge.required' => 'Service charge is required',
            'service_charge.numeric' => 'Service charge must be a number',
        ]);

        $effectiveFrom = $request->effective_from ? Carbon::parse($request->effective_from)->toDateString() : now()->toDateString();

        if (!Schema::hasColumn('recei__adds', 'effective_from')) {
            throw ValidationException::withMessages(['effective_from' => 'Apply manual SQL 007 before adding receipt types so previous values can be retained.']);
        }

        DB::transaction(function () use ($request, $effectiveFrom, $intervals) {
            $active = Recei_Add::where('receiptname', $request->receiptname)
                ->where('is_active', 1)->whereNull('effective_to')->lockForUpdate()->get();
            foreach ($active as $previous) {
                $closingDate = Carbon::parse($effectiveFrom)->subDay()->toDateString();
                $oldStart = $previous->effective_from?->toDateString();
                if ($oldStart && $closingDate < $oldStart) $closingDate = $oldStart;
                $previous->update(['effective_to' => $closingDate, 'is_active' => 0]);
            }
            $receipt = new Recei_Add;
            $receipt->receiptname = $request->receiptname;
            $receipt->rate1 = $request->rate1;
            $receipt->period1 = $request->period1;
            $receipt->rate2 = $request->rate2;
            $receipt->period2 = $request->period2;
            $receipt->rate3 = $request->rate3;
            $receipt->period3 = $request->period3;
            $receipt->validPeriod = $request->valid;
            $receipt->Postage_charge = $request->Postage_charge;
            $receipt->s_charge_less = $request->s_char_less;
            $receipt->s_charge_greater = $request->s_char_grea;
            $receipt->service_charge = $request->service_charge;
            $receipt->effective_from = $effectiveFrom;
            $receipt->effective_to = null;
            $receipt->is_active = 1;
            $receipt->fill($intervals);
            $receipt->save();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Receipt type created successfully'
        ]);
    }

    // Delete receipt using Ajax
    public function delete(Request $request)
    {
        $receipt = Recei_Add::find($request->receipt_id);

        if ($receipt) {
            if (Schema::hasColumn('recei__adds', 'is_active')) {
                $receipt->update([
                    'is_active' => 0,
                    'effective_to' => now()->toDateString(),
                ]);
            } else {
                $receipt->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Receipt type deleted successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Receipt type not found'
        ], 404);
    }

    public function show($id)
    {
        $receipt = Recei_Add::findOrFail($id);
        return view('receipt.show', compact('receipt'));
    }

    public function edit($id)
    {
        $receipt = Recei_Add::findOrFail($id);
        return view('receipt.edit', compact('receipt'));
    }

    // Update receipt using Ajax
    public function update(Request $request)
    {
        $intervals = $this->validatedIntervals($request, 'up_');
        $request->validate([
            'up_receiptname' => 'required|max:80',
            'up_effective_from' => 'nullable|date|before_or_equal:today',
            'up_rate1' => 'required|numeric',
            'up_rate2' => 'required|numeric',
            'up_rate3' => 'nullable|numeric',
            'up_period1' => 'required|numeric',
            'up_period2' => 'required|numeric',
            'up_period3' => 'required|numeric',
            'up_valid' => 'required|numeric',
            'up_s_char_less' => 'required|numeric',
            'up_s_char_grea' => 'required|numeric',
            'up_Postage_charge' => 'required|numeric',
            'up_service_charge' => 'required|numeric',
        ], [
            'up_effective_from.before_or_equal' => 'Future scheduling is not supported. Make this change on its effective date.',
            'up_receiptname.required' => 'Receipt type name is required',
            'up_rate1.required' => 'Rate 1 is required',
            'up_rate1.numeric' => 'Rate 1 must be a number',
            'up_rate2.required' => 'Rate 2 is required',
            'up_rate2.numeric' => 'Rate 2 must be a number',
            'up_rate3.numeric' => 'Rate 3 must be a number',
            'up_period1.required' => 'Period 1 is required',
            'up_period1.numeric' => 'Period 1 must be a number',
            'up_period2.required' => 'Period 2 is required',
            'up_period2.numeric' => 'Period 2 must be a number',
            'up_period3.required' => 'Period 3 is required',
            'up_period3.numeric' => 'Period 3 must be a number',
            'up_valid.required' => 'Valid period is required',
            'up_valid.numeric' => 'Valid period must be a number',
            'up_s_char_less.required' => 'Service charge (< 25000) is required',
            'up_s_char_less.numeric' => 'Service charge (< 25000) must be a number',
            'up_s_char_grea.required' => 'Service charge (> 25000) is required',
            'up_s_char_grea.numeric' => 'Service charge (> 25000) must be a number',
            'up_Postage_charge.required' => 'Postage charge is required',
            'up_Postage_charge.numeric' => 'Postage charge must be a number',
            'up_service_charge.required' => 'Service charge is required',
            'up_service_charge.numeric' => 'Service charge must be a number',
        ]);

        $receipt = Recei_Add::find($request->up_id);

        if ($receipt) {
            $effectiveFrom = $request->up_effective_from ? Carbon::parse($request->up_effective_from)->toDateString() : now()->toDateString();
            if (!Schema::hasColumn('recei__adds', 'effective_from')) {
                throw ValidationException::withMessages(['up_effective_from' => 'Apply manual SQL 007 before editing receipt types so previous values can be retained.']);
            }
            DB::transaction(function () use ($receipt, $request, $effectiveFrom, $intervals) {
                $current = Recei_Add::whereKey($receipt->id)->lockForUpdate()->firstOrFail();
                if ($current->is_active === false || $current->effective_to) {
                    throw ValidationException::withMessages(['up_id' => 'This receipt type has already changed. Reload before editing.']);
                }
                $closingDate = Carbon::parse($effectiveFrom)->subDay()->toDateString();
                $oldStart = $current->effective_from?->toDateString();
                if ($oldStart && $closingDate < $oldStart) {
                    $closingDate = $oldStart; // Same-day edits retain both versions; latest id wins for new receipts.
                }
                $current->update(['effective_to' => $closingDate, 'is_active' => 0]);
                if ($current->receiptname !== $request->up_receiptname) {
                    Recei_Add::where('receiptname', $request->up_receiptname)->where('is_active', 1)
                        ->update(['effective_to' => $closingDate, 'is_active' => 0]);
                }
                $new = $current->replicate();
                $new->fill([
                    'receiptname' => $request->up_receiptname,
                    'rate1' => $request->up_rate1, 'rate2' => $request->up_rate2,
                    'rate3' => $request->up_rate3, 'period1' => $request->up_period1,
                    'period2' => $request->up_period2, 'period3' => $request->up_period3,
                    'validPeriod' => $request->up_valid,
                    's_charge_less' => $request->up_s_char_less,
                    's_charge_greater' => $request->up_s_char_grea,
                    'Postage_charge' => $request->up_Postage_charge,
                    'service_charge' => $request->up_service_charge,
                    'effective_from' => $effectiveFrom, 'effective_to' => null, 'is_active' => 1,
                ] + $intervals);
                $new->save();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Receipt type updated successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Receipt type not found'
        ], 404);
    }

    public function destroy($id)
    {
        $data = Recei_Add::find($id);

        if ($data) {
            $data->delete();
            return redirect()->back()->with("delete", "Receipt deleted successfully");
        }

        return redirect()->back()->with("error", "Receipt not found");
    }

    private function validatedIntervals(Request $request, string $prefix = ''): array
    {
        if (!Schema::hasColumn('recei__adds', 'letter_1_days')) {
            throw ValidationException::withMessages(['intervals' => 'Receipt-type interval setup is pending. Please ask your administrator to apply manual SQL 004.']);
        }
        $rules = [];
        foreach (ReceiptPenaltySchedule::FIELDS as $field) {
            $rules[$prefix.$field] = ['required', 'integer', 'min:0', 'max:3650'];
        }
        $validated = $request->validate($rules);
        $values = [];
        foreach (ReceiptPenaltySchedule::FIELDS as $field) $values[$field] = (int) $validated[$prefix.$field];
        return $values;
    }
}
