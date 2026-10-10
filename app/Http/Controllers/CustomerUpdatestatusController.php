<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerContactSyncService;
use App\Services\CustomerPawnPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomerUpdatestatusController extends Controller
{
    public function customerUpdatestatus(Request $request, CustomerPawnPolicy $policy)
    {
        $request->validate(['nic' => 'nullable|string|max:20', 'bc' => 'nullable|string|max:10']);
        $nic = trim((string) $request->input('nic', ''));
        $customer = null;
        $pawnStats = null;
        $effectivePolicy = null;
        $branchChoices = collect();

        if ($nic !== '') {
            $matches = $policy->customers($nic);
            $isAdmin = auth()->user()->role === 'Admin'; // Developer accounts also have the Admin role.
            $branchChoices = $isAdmin ? $matches->whereNotNull('BC')->unique('BC')->values() : collect();
            $targetBranch = $isAdmin ? $request->input('bc', auth()->user()->BC) : auth()->user()->BC;
            $customer = $matches->where('BC', $targetBranch)->sortByDesc('id')->first();
            if (!$customer && $isAdmin) {
                $customer = $matches->whereNotNull('BC')->sortByDesc('id')->first()
                    ?: $matches->sortByDesc('id')->first();
            }
            if ($customer) {
                $pawnStats = (object) $policy->exposure($nic);
                $effectivePolicy = $policy->policy($matches);
            }
        }

        return view('customerUpdatestatus', compact('customer', 'nic', 'pawnStats', 'effectivePolicy', 'branchChoices'));
    }

    public function updateStatus(Request $request, $id, CustomerContactSyncService $contactSync, CustomerPawnPolicy $policy)
    {
        $values = $request->validate([
            'first_name' => 'required|string|max:100',
            'contact_1' => 'required|string|max:20',
            'contact_2' => 'nullable|string|max:20',
            'address_1' => 'required|string|max:500',
            'nic' => 'required|string|max:20',
            'driving_license' => 'nullable|string|max:50',
            'passport' => 'nullable|string|max:50',
            'other_identifications' => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
            'limit_amount' => 'nullable|numeric|min:0',
            'limit_pawn_count' => 'nullable|integer|min:0',
        ]);

        try {
            $updated = DB::transaction(function () use ($id, $values, $contactSync, $policy) {
                $query = Customer::whereKey($id);
                if (auth()->user()->role !== 'Admin') {
                    $query->where('BC', auth()->user()->BC);
                }
                $customer = $query->lockForUpdate()->firstOrFail();
                $oldNic = (string) $customer->NIC;
                $newNic = trim($values['nic']);
                if (CustomerPawnPolicy::key($oldNic) !== CustomerPawnPolicy::key($newNic)
                    && $policy->customers($newNic)->isNotEmpty()) {
                    throw ValidationException::withMessages(['nic' => 'This NIC already belongs to another customer.']);
                }

                $before = ['NIC' => $oldNic, 'Name' => $customer->Name,
                    'Address_1' => $customer->Address_1, 'Contact_1' => $customer->Contact_1];
                $policy->updateIdentityPolicy($oldNic, (int) $values['status'],
                    isset($values['limit_amount']) ? (float) $values['limit_amount'] : null,
                    isset($values['limit_pawn_count']) ? (int) $values['limit_pawn_count'] : null);
                $customer->update([
                    'First_name' => $values['first_name'],
                    'Name' => trim(implode(' ', array_filter([$values['first_name'], $customer->Middle_name, $customer->Last_name]))),
                    'Address_1' => $values['address_1'],
                    'Contact_1' => $values['contact_1'],
                    'Contact_2' => $values['contact_2'] ?? null,
                    'NIC' => $newNic,
                    'Driving_license' => $values['driving_license'] ?? null,
                    'Passport' => $values['passport'] ?? null,
                    'Other_identifications' => $values['other_identifications'] ?? null,
                    'Status' => (int) $values['status'],
                    'Limit_Amount' => $values['limit_amount'] ?? null,
                    'Limit_Pawn_Count' => $values['limit_pawn_count'] ?? null,
                    'OC' => auth()->user()->username,
                ]);
                if ($before['NIC'] !== $newNic || $before['Name'] !== $customer->Name
                    || $before['Address_1'] !== $customer->Address_1
                    || $before['Contact_1'] !== $customer->Contact_1) {
                    $contactSync->syncActiveSnapshots($customer->fresh(), $oldNic, $before);
                }
                Log::info('Customer policy updated', ['customer_id' => $customer->id, 'updated_by' => auth()->id()]);
                return ['nic' => $newNic, 'bc' => $customer->BC];
            });
            return redirect()->route('customerUpdatestatus', array_filter($updated))
                ->with('success', 'Customer details and limits updated.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Customer update failed', ['customer_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Customer update could not be saved.')->withInput();
        }
    }
}
