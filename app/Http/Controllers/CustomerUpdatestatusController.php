<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CustomerContactSyncService;

class CustomerUpdatestatusController extends Controller
{
    public function customerUpdatestatus(Request $request)
    {
        $nic = $request->nic;
        $customer = null;
        $pawnStats = null;

        if ($nic) {
            $customer = Customer::where('NIC', $nic)->first();

            if ($customer) {
                $pawnStats = DB::table('t_pawn_sums')
                    ->where('Customer_NIC', $customer->NIC)
                    ->selectRaw('COALESCE(SUM(Pawn_Amount), 0) as total_pawn_amount, COUNT(*) as pawn_count')
                    ->first();
            }
        }

        return view('customerUpdatestatus', compact('customer', 'nic', 'pawnStats'));
    }

    public function updateStatus(Request $request, $id, CustomerContactSyncService $contactSync)
    {
        try {
            // 1. Comprehensive Validation
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:10',
                'gender' => 'nullable|string|max:10',
                'first_name' => 'required|string|max:100',
                'name' => 'nullable|string|max:255',
                'code' => 'nullable|string|max:50',
                'contact_1' => 'required|string|max:20',
                'contact_2' => 'nullable|string|max:20',
                'address_1' => 'required|string|max:500',
                'address_2' => 'nullable|string|max:500',
                'city_2' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:100',
                'nic' => 'required|string|max:20',
                'driving_license' => 'nullable|string|max:50',
                'passport' => 'nullable|string|max:50',
                'other_identifications' => 'nullable|string|max:500',
                'status' => 'required|in:0,1',
                'bc' => 'nullable|string|max:100',
                'oc' => 'nullable|string|max:100',
                'limit_amount' => 'nullable|numeric|min:0',
                'limit_pawn_count' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            $customer = Customer::where('id', $id)->where('BC', auth()->user()->BC)->lockForUpdate()->firstOrFail();

            // 2. Unique NIC Check (excluding current customer)
            if ($request->nic != $customer->NIC) {
                $exists = Customer::where('NIC', $request->nic)->where('id', '!=', $id)->exists();
                if ($exists) {
                    return redirect()->back()->with('error', 'This NIC is already registered.')->withInput();
                }
            }

            $oldData = $customer->toArray();
            $oldNic = $customer->NIC;

            // 3. Update (Ensure keys match your DB column names exactly)
            $customer->update([
                'Code' => $request->code,
                'Title' => $request->title,
                'Gender' => $request->gender,
                'Name' => $request->name,
                'First_name' => $request->first_name,
                'Middle_name' => $request->middle_name,
                'Last_name' => $request->last_name,
                'Address_1' => $request->address_1,
                'City_1' => $request->city_1,
                'Address_2' => $request->address_2,
                'City_2' => $request->city_2,
                'Contact_1' => $request->contact_1,
                'Contact_2' => $request->contact_2,
                'Email' => $request->email,
                'NIC' => $request->nic,
                'Driving_license' => $request->driving_license,
                'Passport' => $request->passport,
                'Other_identifications' => $request->other_identifications,
                'Status' => $request->status,
                'BC' => auth()->user()->BC,
                'OC' => auth()->user()->username,
                'Limit_Amount' => $request->limit_amount,
                'Limit_Pawn_Count' => $request->limit_pawn_count,
            ]);

            $contactSync->syncActiveSnapshots($customer->fresh(), $oldNic, [
                'NIC' => $oldData['NIC'] ?? null,
                'Address_1' => $oldData['Address_1'] ?? null,
                'Contact_1' => $oldData['Contact_1'] ?? null,
            ]);

            Log::info('Customer updated', [
                'customer_id' => $id,
                'updated_by' => auth()->id() ?? 'system',
                'changes' => $this->getChangedFields($oldData, $customer->fresh()->toArray())
            ]);

            DB::commit();

            return redirect()->route('customerUpdatestatus', ['nic' => $customer->NIC])
                             ->with('success', 'Customer record updated successfully!');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Update Failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Update Error: ' . $e->getMessage()) // Shows real error temporarily
                ->withInput();
        }
    }

    private function getChangedFields($oldData, $newData)
    {
        $changes = [];
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData) && $oldData[$key] != $value) {
                $changes[$key] = ['old' => $oldData[$key], 'new' => $value];
            }
        }
        return $changes;
    }
}
