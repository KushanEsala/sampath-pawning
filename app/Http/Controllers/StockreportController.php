<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\TPawnSum; // Replace with your actual model
use App\Models\TOpeningPawnSum;

use App\Models\Datatables;

class StockreportController extends Controller
{

public function search(Request $request)
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
                        
   




    // Construct a query to filter records based on search criteria
    // $query = TPawnSum::query();

    if ($fromDate && $toDate) {
        // $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
        // ->where('IsRedeemed', 0)
        // ->where('isForfeit', 0)
        // ->get();
        
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

    // Add more filters as needed based on your search form
    // $recipts = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])->get();

    // Calculate the sum of the desired column (e.g., 'amount_column')
    $total = $query->sum('Amount');
    $totalAmount = number_format($total, 2);
    $int =  $query->sum('Interest');
    $interest = number_format($int, 2);
    $pawnCount = $query->count();
    $TotalPawn = TPawnSum::count();



    return view('Stockreport')
    -> with("recipts", $query)
    -> with("amount", $totalAmount)
    -> with("Interest", $interest)
    -> with("pawnCount",$pawnCount);
}

}



// $BankDetails = BankDetails::all();
// return view('Bank_Branch')
// -> with("itemCategory", $BankDetails);
