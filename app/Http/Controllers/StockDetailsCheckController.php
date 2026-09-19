<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\TPawnSum; // Replace with your actual model
use App\Models\TOpeningPawnSum;

use App\Models\Datatables;

class StockDetailsCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function StockCheck(Request $request)
    {
    
        // Get search criteria from the form input
        $fromDate  = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        $branch_code = auth()->user()->BC;
    
        $query1 = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                            ->where('IsRedeemed', 0)
                            ->where('isForfeit', 0)
                            ->where('BC',$branch_code)
                            ->get();
                            
        $query2 = TOpeningPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                            ->where('IsRedeemed', 0)
                            ->where('isForfeit', 0)
                            ->where('BC',$branch_code)
                            ->get();
        
        $query = $query1->concat($query2);
                            
    
        if ($fromDate && $toDate) {
            
        $query1 = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                            ->where('IsRedeemed', 0)
                            ->where('isForfeit', 0)
                            ->where('BC',$branch_code)
                            ->get();
                            
        $query2 = TOpeningPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                            ->where('IsRedeemed', 0)
                            ->where('isForfeit', 0)
                            ->where('BC',$branch_code)
                            ->get();
        
        $query = $query1->concat($query2);
       
    
    
        }
    
        $total = $query->sum('Amount');
        $totalAmount = number_format($total, 2);
        $int =  $query->sum('Interest');
        $interest = number_format($int, 2);
        $pawnCount = $query->count();
        $TotalPawn = TPawnSum::count();
    
    
    
        return view('StockCheckreport')
        -> with("recipts", $query)
        -> with("amount", $totalAmount)
        -> with("Interest", $interest)
        -> with("pawnCount",$pawnCount);
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
            'Receipt_Number' => 'required|integer',
            'checked' => 'required|boolean',
        ]);
    
        // Find the record and update the checkbox state
        $record = TPawnSum::find($request->Receipt_Number);
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
    // Update the checkbox_stock field for all receipts to 0
    DB::table('t_pawn_sums')->update(['checkbox_stock' => 0]);

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
