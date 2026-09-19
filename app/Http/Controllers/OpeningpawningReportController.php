<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\TOpeningPawnSum; // Replace with your actual model
use App\Models\TOpeningPawnDetails;

class OpeningpawningReportController extends Controller
{
    public function index(Request $request){
    // Get search criteria from the form input
    $branch_code = auth()->user()->BC;
    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');

    $query = TOpeningPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
            ->get();

    // Construct a query to filter records based on search criteria
    $query = TOpeningPawnSum::query();
    if ($fromDate && $toDate) {
        $query = TOpeningPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                ->where('BC', $branch_code)
                ->get();
        // $query = TPawnSum::select(
        //     't_pawn_sums.*',
        //     't_pawn_details.*'
        // )
        // ->join('t_pawn_details', 't_pawn_sums.Receipt_Number', '=', 't_pawn_details.Receipt_Number')
        // ->whereBetween('t_pawn_sums.Receipt_Date', [$fromDate, $toDate])
        // ->where('t_pawn_sums.BC', $branch_code)
        // ->where('t_pawn_details.BC', $branch_code)
        // ->groupBy('t_pawn_sums.Receipt_Number')
        // ->get();
    }


    // $query = TPawnSum::select(
    //     't_pawn_sums.*',
    //     't_pawn_details.*'
    // )
    // ->join('t_pawn_details', 't_pawn_sums.Receipt_Number', '=', 't_pawn_details.Receipt_Number')
    // ->whereBetween('t_pawn_sums.Receipt_Date', [$fromDate, $toDate])
    // ->where('BC', $branch_code)
    // ->groupBy('t_pawn_sums.Receipt_Number')
    // ->get();


    // Calculate the sum of the desired column (e.g., 'amount_column')
    $total_weight = $query->sum('Total_Weight');
    $totalWeight = number_format($total_weight, 2);

    $pawn_weight = $query->sum('Pawn_Weight');
    $pawnWeight = number_format($pawn_weight, 2);

    $total = $query->sum('Amount');
    $totalAmount = number_format($total, 2);
    $int =  $query->sum('Interest');
    $interest = number_format($int, 2);
    $TotalPawn = TOpeningPawnSum::count();

    return view('openingPawningReport')
    ->with("recipts", $query)
    -> with("recipts", $query)
    -> with("totalAmount", $totalAmount)
    -> with("totalWeight", $totalWeight)
    -> with("pawnWeight", $pawnWeight)
    -> with("Interest", $interest);
    }
}
