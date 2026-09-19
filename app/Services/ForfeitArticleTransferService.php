<?php

namespace App\Services;

use App\Models\GetItemData;
use App\Models\Item;
use App\Models\ReceiptLifecycleEvent;
use App\Models\TForfeitSum;
use App\Models\TPawnSum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ForfeitArticleTransferService
{
    public function transfer(array $receiptIds, User $operator): array
    {
        if (!Schema::hasTable('receipt_lifecycle_events')) {
            throw ValidationException::withMessages(['selected_receipts' => 'Apply the existing manual receipt_lifecycle_events SQL script before transferring articles.']);
        }

        $lockName = 'sampath_forfeit_article_stock_codes';
        $lock = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);
        if ((int) ($lock->acquired ?? 0) !== 1) {
            throw ValidationException::withMessages(['selected_receipts' => 'Another stock transfer is in progress. Please try again.']);
        }
        try {
            return DB::transaction(function () use ($receiptIds, $operator) {
            $ids = array_values(array_unique($receiptIds));
            $receipts = TPawnSum::whereIn('id', $ids)->where('BC', $operator->BC)
                ->where('isForfeit', 1)->where('IsRedeemed', 0)->orderBy('id')->lockForUpdate()->get();
            if ($receipts->count() !== count($ids)) {
                throw ValidationException::withMessages(['selected_receipts' => 'Select only forfeited receipts belonging to your branch.']);
            }

            // Lock the global code range, matching the existing global stock numbering.
            $latest = GetItemData::orderByDesc('Item_code')->lockForUpdate()->first();
            $nextCode = $latest ? (int) $latest->Item_code + 1 : 10000;
            $eventIds = [];
            foreach ($receipts as $receipt) {
                $items = Item::where('BC', $operator->BC)->where('Receipt_Number', $receipt->Receipt_Number)
                    ->where('SaleIsItem', 0)->where('IntoItem', 0)->orderBy('id')->lockForUpdate()->get();
                if ($items->isEmpty()) {
                    throw ValidationException::withMessages(['selected_receipts' => 'A selected receipt has already been transferred or has no available articles. Refresh the list.']);
                }
                $forfeit = TForfeitSum::where('BC', $operator->BC)->where('Receipt_Number', $receipt->Receipt_Number)
                    ->orderByDesc('Forfeit_Number')->first();
                $customer = \App\Models\Customer::where('NIC', $receipt->Customer_NIC)
                    ->where(fn ($query) => $query->where('BC', $operator->BC)->orWhereNull('BC'))
                    ->orderByRaw('BC IS NULL')->first();
                $capitalPaid = DB::table('t_pawn_trans')->where('BC', $receipt->BC)->where('code', $receipt->Receipt_Number)
                    ->where('trans_type', 'PART_PAYMENT')
                    ->sum('Paided_Captional');
                $articles = [];
                foreach ($items as $item) {
                    $stock = GetItemData::create([
                        'Receipt_Number' => $item->Receipt_Number, 'category' => $item->category,
                        'Item_code' => str_pad($nextCode++, 5, '0', STR_PAD_LEFT),
                        'Item_description' => $item->Item_description, 'Brand' => $item->Brand,
                        'Karatage' => $item->Make, 'purchasePrice' => $item->purchasePrice,
                        'saleprice' => $item->saleprice, 'Total_Weight' => $item->Total_Weight ?? $item->Weight,
                        'Weight' => $item->Weight, 'QTY' => $item->QTY,
                        'Branch' => $operator->Branch, 'BC' => $operator->BC,
                        'Bar_code' => str_pad($nextCode - 1, 5, '0', STR_PAD_LEFT).'/'.now()->format('Ymd').'/'.$item->Receipt_Number,
                    ]);
                    $item->update(['IntoItem' => 1]);
                    $articles[] = $stock->only(['Item_code','category','Item_description','Brand','Karatage','Weight','Total_Weight','QTY','purchasePrice']);
                }
                // Freeze financial figures at forfeiture, not accruing interest after disposal.
                $interestOutstanding = $forfeit?->Payable_Interest !== null && $forfeit->Payable_Interest !== '' && (float) $forfeit->Payable_Interest > 0
                    ? (float) $forfeit->Payable_Interest
                    : null;
                $interestDays = null;

                if ($interestOutstanding === null) {
                    $calcDate = $forfeit?->Forfeit_Date ?: now();
                    $financial = app(ReceiptFinancialCalculator::class)->calculate($receipt, $calcDate);
                    $interestOutstanding = (float) $financial['interest'];
                    $interestDays = (int) $financial['days'];
                } elseif ($receipt->Receipt_Date) {
                    $interestDays = ReceiptInterestPeriod::days($receipt, $forfeit?->Forfeit_Date ?: now());
                }

                $data = [
                    'stock_number' => $receipt->Invoice_Number, 'ticket_number' => $receipt->Ticket_Number,
                    'customer_name' => $customer?->Name ?: $receipt->Customer_Name, 'customer_nic' => $receipt->Customer_NIC,
                    'customer_address' => $customer?->Address_1 ?: $receipt->Customer_Address,
                    'customer_phone' => $customer?->Contact_1 ?: $receipt->Customer_Phone,
                    'receipt_date' => $receipt->Receipt_Date?->toDateString(),
                    'expiry_date' => $receipt->Final_date?->toDateString(),
                    'forfeited_date' => $forfeit?->Forfeit_Date ? (string) $forfeit->Forfeit_Date : null,
                    'capital_outstanding' => $forfeit?->Payable_Pawn_Amount ?? $receipt->Pawn_Amount,
                    'interest_outstanding' => $interestOutstanding,
                    'interest_days' => $interestDays,
                    'interest_paid' => $forfeit?->Paid_Interest,
                    'capital_paid' => $capitalPaid,
                    'operator' => $operator->only(['username','name','role','Branch','BC']), 'articles' => $articles,
                ];
                $event = ReceiptLifecycleEvent::create([
                    'pawn_sum_id' => $receipt->id, 'BC' => $receipt->BC, 'receipt_number' => $receipt->Receipt_Number,
                    'event_type' => 'ARTICLES_TRANSFERRED_TO_STOCK', 'event_date' => now(),
                    'description' => 'All available forfeited articles transferred to sale stock; no customer payment recorded.',
                    'created_by' => $operator->username, 'event_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
                $eventIds[] = $event->id;
            }
            return $eventIds;
            }, 3);
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }
}
