<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TOpeningPawnDetails;

class OpeningpawningArticleReportController extends Controller
{
    public function index(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $redeem = TOpeningPawnDetails::whereBetween('Date', [$fromDate, $toDate])
                    ->where('BC', $branch_code)
                    ->get();

        $query = TOpeningPawnDetails::query();

        if ($fromDate && $toDate) {
            $query = TOpeningPawnDetails::whereBetween('Date', [$fromDate, $toDate])
                        ->where('BC', $branch_code)
                        ->get();
        }

        $total_weight = $query->sum('Total_Weight');
        $totalWeight = number_format($total_weight, 2);

        $pawn_weight = $query->sum('Weight');
        $pawnWeight = number_format($pawn_weight, 2);

        $pawn_qty = $query->sum('QTY');
        $pawnqty = number_format($pawn_qty, 2);

        return view('openingpawingarticlereport')
        ->with("redeem", $redeem)
        -> with("recipts", $query)
        -> with("totalWeight", $totalWeight)
        -> with("pawnWeight", $pawnWeight)
        -> with("pawnqty", $pawnqty);

    }
}
