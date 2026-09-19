<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TPawnSum;
use App\Services\ReceiptHistoryService;
use Illuminate\Http\Request;

class ReceiptSearchController extends Controller
{
    public function index(Request $request, ReceiptHistoryService $historyService)
    {
        $history = null;
        $tickets = null;
        $customer = null;

        $searchType = $request->input('search_type', 'ticket');
        $searchQuery = trim((string) ($request->input('search_query') ?? $request->input('receipt_number') ?? ''));

        if ($searchQuery !== '') {
            $branchCode = auth()->user()->BC;

            if ($searchType === 'nic') {
                $tickets = TPawnSum::where('Customer_NIC', $searchQuery)
                    ->where('BC', $branchCode)
                    ->orderByDesc('Receipt_Date')
                    ->orderByDesc('id')
                    ->get();

                $customer = Customer::where('NIC', $searchQuery)
                    ->where(function ($q) use ($branchCode) {
                        $q->where('BC', $branchCode)->orWhereNull('BC');
                    })
                    ->orderByRaw('BC IS NULL')
                    ->first();
            } else {
                // Search by Ticket Number, Receipt Number, or Stock (Invoice) Number
                $receipt = TPawnSum::where(function ($q) use ($searchQuery) {
                        $q->where('Ticket_Number', $searchQuery)
                          ->orWhere('Receipt_Number', $searchQuery)
                          ->orWhere('Invoice_Number', $searchQuery);
                    })
                    ->where('BC', $branchCode)
                    ->first();

                if ($receipt) {
                    $history = $historyService->build($receipt);
                }
            }
        }

        return view('receiptSearch', compact('history', 'tickets', 'customer', 'searchType', 'searchQuery'));
    }

    public function print(Request $request, ReceiptHistoryService $historyService)
    {
        $request->validate(['receipt_number' => ['required']]);
        $receipt = TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', auth()->user()->BC)->firstOrFail();
        $history = $historyService->build($receipt);

        return view('receiptHistoryPrint', compact('history'));
    }
}
