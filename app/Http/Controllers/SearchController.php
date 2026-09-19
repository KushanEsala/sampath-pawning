<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\TPawnSum; // Replace with your actual model

class SearchController extends Controller
{
    public function search(Request $request){
    // Get search criteria from the form input
    $branch_code = auth()->user()->BC;
    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');

    $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                ->where('BC', $branch_code)
                ->get();
                
    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');

    // Construct a query to filter records based on search criteria
    $query = TPawnSum::query();
    if ($fromDate && $toDate) {
        $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                    ->where('BC', $branch_code)
                    ->get();
    }

    // Calculate the sum of the desired column (e.g., 'amount_column')
    $total_weight = $query->sum('Total_Weight');
    $totalWeight = number_format($total_weight, 2);

    $pawn_weight = $query->sum('Pawn_Weight');
    $pawnWeight = number_format($pawn_weight, 2);
    
    $total = $query->sum('Amount');
    $totalAmount = number_format($total, 2);
    $int =  $query->sum('Interest');
    $interest = number_format($int, 2);
    $TotalPawn = TPawnSum::count();

    return view('search_results')
    ->with("recipts", $query)
    -> with("recipts", $query)
    -> with("totalAmount", $totalAmount)
    -> with("totalWeight", $totalWeight)
    -> with("pawnWeight", $pawnWeight)
    -> with("Interest", $interest);
    }
}
