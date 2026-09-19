<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TRedeemSum;

class RedeemReportController extends Controller
{
    public function redeem(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $redeem = TRedeemSum::whereBetween('Redeem_Date', [$fromDate, $toDate])
                        ->where('BC', $branch_code)
                        ->get();

        $query = TRedeemSum::query();

        if ($fromDate && $toDate) {
            $query = TRedeemSum::whereBetween('Redeem_Date', [$fromDate, $toDate])
                        ->where('BC', $branch_code)
                        ->get();

        }
        
         $total_weight = $query->sum('Total_Weight');
        $totalWeight = number_format($total_weight, 2);

        $pawn_weight = $query->sum('Pawn_Weight');
        $pawnWeight = number_format($pawn_weight, 2);
        
        $total = $query->sum('Payable_Total');
        $totalAmount = number_format($total, 2);
        $int =  $query->sum('Paid_Interest');
        $interest = number_format($int, 2);
        $TotalPawn = TRedeemSum::count();


        return view('redeem_report')
        ->with("redeem", $redeem)
        -> with("recipts", $query)
        -> with("totalAmount", $totalAmount)
        -> with("totalWeight", $totalWeight)
        -> with("pawnWeight", $pawnWeight)
        -> with("Interest", $interest);

    }
}
