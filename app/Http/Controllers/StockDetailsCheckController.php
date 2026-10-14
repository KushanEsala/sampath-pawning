<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\TPawnSum;
use App\Models\TOpeningPawnSum;

class StockDetailsCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function StockCheck(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $view = $request->query('view') === 'checked' ? 'checked' : 'pending';
        $branchCode = auth()->user()->BC;

        $pawnQuery = TPawnSum::where('IsRedeemed', 0)
            ->where('isForfeit', 0)
            ->where('BC', $branchCode);
        if ($view === 'checked') {
            $pawnQuery->where('checkbox_stock', 1);
        } else {
            $pawnQuery->where(function ($query) {
                $query->where('checkbox_stock', 0)->orWhereNull('checkbox_stock');
            });
        }
        if ($fromDate) $pawnQuery->where('Receipt_Date', '>=', $fromDate);
        if ($toDate) $pawnQuery->where('Receipt_Date', '<=', $toDate);
        if ($view === 'pending' && !$fromDate && !$toDate) $pawnQuery->whereRaw('1 = 0');
        $query = $pawnQuery->get();

        if ($view === 'pending') {
            $openingQuery = TOpeningPawnSum::where('IsRedeemed', 0)
                ->where('isForfeit', 0)
                ->where('BC', $branchCode);
            if ($fromDate) $openingQuery->where('Receipt_Date', '>=', $fromDate);
            if ($toDate) $openingQuery->where('Receipt_Date', '<=', $toDate);
            if (!$fromDate && !$toDate) $openingQuery->whereRaw('1 = 0');
            $query = $query->concat($openingQuery->get());
        }

        $total = $query->sum('Amount');
        $totalAmount = number_format($total, 2);
        $int =  $query->sum('Interest');
        $interest = number_format($int, 2);
        $pawnCount = $query->count();

        return view('StockCheckreport')
        -> with("recipts", $query)
        -> with("amount", $totalAmount)
        -> with("Interest", $interest)
        -> with("pawnCount",$pawnCount)
        -> with('view', $view);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCheckbox(Request $request)
    {
        // Validate input
        $request->validate([
            'record_id' => 'required|integer',
            'checked' => 'required|boolean',
        ]);
    
        // Find the record and update the checkbox state
        $record = TPawnSum::whereKey($request->record_id)
            ->where('BC', auth()->user()->BC)->first();
        if ($record) {
            $record->checkbox_stock = $request->checked;  // Update with correct column name
            $record->save();
    
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false], 404);
    }
    
    
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
 // In your ReceiptController.php

public function resetCheckboxStock(Request $request)
{
    DB::table('t_pawn_sums')->where('BC', auth()->user()->BC)
        ->where('IsRedeemed', 0)->where('isForfeit', 0)
        ->where('checkbox_stock', 1)->update(['checkbox_stock' => 0]);

    return response()->json(['success' => true]);
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
