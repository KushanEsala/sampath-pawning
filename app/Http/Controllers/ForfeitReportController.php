<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TPawnSum;
use App\Models\TOpeningPawnSum;
use App\Models\TForfitDetails;
use App\Models\Item;
use App\Models\GetItemData;
use Carbon\Carbon;


class ForfeitReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
 public function index(Request $request)
    {
    // Get search criteria from the form input
    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');
    $branch_code =auth()->user()->BC;

    $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
            ->where('IsRedeemed', 0)
             ->where('BC', $branch_code)
            ->where('isForfeit', 1)
            ->get();

    if ($fromDate && $toDate) {
        $dataTPawnSum = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                        ->where('IsRedeemed', 0)
                        ->where('BC', $branch_code)
                        ->where('isForfeit', 1)
                        ->get();

        $dataTOpeningPawnSum = TOpeningPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                                ->where('IsRedeemed', 0)
                                ->where('isForfeit', 1)
                                  ->where('BC', $branch_code)
                                ->get();

        $data = $dataTPawnSum->concat($dataTOpeningPawnSum);


        $total = $data->sum('Amount');
        $totalAmount = number_format($total, 2);
        $int =  $data->sum('Interest');
        $interest = number_format($int, 2);

        return view('forfeitReport')
         ->with("recipts", $data)
        -> with("recipts", $data)
        -> with("amount", $totalAmount)
        -> with("Interest", $interest);
    }

    // Construct a query to filter records based on search criteria
    $query = TPawnSum::query();

    $total = $query->sum('Amount');
    $totalAmount = number_format($total, 2);
    $int =  $query->sum('Interest');
    $interest = number_format($int, 2);
    $TotalPawn = TPawnSum::count();

    return view('forfeitReport')
    -> with("recipts", $query)
    -> with("recipts", $query)
    -> with("amount", $totalAmount)
    -> with("Interest", $interest);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
public function ForfeitArticleList(Request $request)
{
    $branch_code = auth()->user()->BC;

    // Start query: filter by branch and not moved to items yet
    $query = TPawnSum::where('BC', $branch_code)->where('isForfeit', 1)->where('IsRedeemed', 0)
        ->whereExists(function ($items) {
            $items->selectRaw('1')->from('items')->whereColumn('items.BC', 't_pawn_sums.BC')
                ->whereColumn('items.Receipt_Number', 't_pawn_sums.Receipt_Number')
                ->where('items.SaleIsItem', 0)->where('items.IntoItem', 0);
        });


    // Apply date range filter if provided
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('Receipt_Date', [
            Carbon::parse($request->from_date)->startOfDay(),
            Carbon::parse($request->to_date)->endOfDay()
        ]);
    }

    // Execute query and get results
    if ($request->filled('receipt_number')) $query->where('Receipt_Number', $request->receipt_number);
    $size = (int) $request->input('per_page', 25);
    $TForfitDetails = $query->orderByDesc('id')->paginate(in_array($size, [10,25,50,100], true) ? $size : 25)->withQueryString();
    $items = Item::where('BC', $branch_code)->whereIn('Receipt_Number', $TForfitDetails->pluck('Receipt_Number'))
        ->where('SaleIsItem', 0)->where('IntoItem', 0)->get()->groupBy('Receipt_Number');
    $customers = \App\Models\Customer::whereIn('NIC', $TForfitDetails->pluck('Customer_NIC'))
        ->where(fn ($query) => $query->where('BC', $branch_code)->orWhereNull('BC'))->get()->groupBy('NIC');
    $TForfitDetails->each(function ($receipt) use ($items, $customers, $branch_code) {
        $matches = $customers->get($receipt->Customer_NIC, collect());
        $customer = $matches->firstWhere('BC', $branch_code) ?? $matches->first();
        $receipt->setAttribute('available_articles', $items->get($receipt->Receipt_Number, collect()));
        $receipt->setAttribute('current_customer_name', $customer?->Name ?: $receipt->Customer_Name);
    });

    // Pass data to view
    return view('forfeit_article_receipt')->with('recipts', $TForfitDetails);
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function storeFromForfeit(Request $request, \App\Services\ForfeitArticleTransferService $transfer)
{
    $request->validate(['selected_receipts' => 'required|array|min:1|max:100', 'selected_receipts.*' => 'required|integer|distinct']);
    $ids = $transfer->transfer($request->selected_receipts, auth()->user());
    return redirect()->route('forfeit_article_receipt')->with('success', 'Selected receipts transferred to sale stock.')
        ->with('forfeit_print_ids', $ids);
}

public function transferPrint(Request $request)
{
    $request->validate(['events' => 'required|array|min:1|max:100', 'events.*' => 'required|integer']);
    $events = \App\Models\ReceiptLifecycleEvent::whereIn('id', $request->events)
        ->where('BC', auth()->user()->BC)->where('event_type', 'ARTICLES_TRANSFERRED_TO_STOCK')->orderBy('id')->get();
    abort_if($events->isEmpty() || $events->count() !== count(array_unique($request->events)), 404);
    return view('forfeitArticleTransferPrint', compact('events'));
}



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
