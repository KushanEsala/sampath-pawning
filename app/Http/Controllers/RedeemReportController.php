<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TRedeemSum;

class RedeemReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function redeem(Request $request)
    {
        $branch_code = auth()->user()?->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = TRedeemSum::query();

        if ($branch_code) {
            $query->where('BC', $branch_code);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('Redeem_Date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('Redeem_Date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('Redeem_Date', '<=', $toDate);
        }

        $redeem = $query->orderBy('Redeem_Date', 'desc')->get();

        $total_weight = $redeem->sum('Total_Weight');
        $totalWeight = number_format($total_weight, 2);

        $pawn_weight = $redeem->sum('Pawn_Weight');
        $pawnWeight = number_format($pawn_weight, 2);

        $total = $redeem->sum('Payable_Total');
        $totalAmount = number_format($total, 2);
        $int = $redeem->sum('Paid_Interest');
        $interest = number_format($int, 2);

        return view('redeem_report')
            ->with("redeem", $redeem)
            ->with("recipts", $redeem)
            ->with("totalAmount", $totalAmount)
            ->with("totalWeight", $totalWeight)
            ->with("pawnWeight", $pawnWeight)
            ->with("Interest", $interest);
    }
}
