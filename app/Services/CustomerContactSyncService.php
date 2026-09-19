<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\TPawnSum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerContactSyncService
{
    public function __construct(private ReceiptLifecycleService $lifecycle) {}

    public function syncActiveSnapshots(Customer $customer, string $oldNic, array $before): void
    {
        $name = trim((string) ($customer->Name ?: implode(' ', array_filter([
            $customer->First_name, $customer->Middle_name, $customer->Last_name,
        ]))));
        $snapshot = [
            'Customer_NIC' => $customer->NIC,
            'Customer_Name' => $name,
            'Customer_Address' => $customer->Address_1,
            'Customer_Phone' => $customer->Contact_1,
        ];

        $activeReceipts = TPawnSum::where('Customer_NIC', $oldNic)
            ->where('BC', $customer->BC)->where('IsRedeemed', 0)->where('isForfeit', 0)->get();

        foreach ($activeReceipts as $receipt) {
            $receipt->update($snapshot);
            $this->lifecycle->record($receipt->fresh(), 'CUSTOMER_CONTACT_UPDATED', null, 'Canonical customer contact details updated.', [
                'before' => $before,
                'after' => [
                    'NIC' => $customer->NIC,
                    'Address_1' => $customer->Address_1,
                    'Contact_1' => $customer->Contact_1,
                ],
            ]);
        }

        if (Schema::hasTable('t_opening_pawn_sums')) {
            DB::table('t_opening_pawn_sums')->where('Customer_NIC', $oldNic)
                ->where('BC', $customer->BC)->where('IsRedeemed', 0)->where('isForfeit', 0)
                ->update($snapshot);
        }
    }
}
