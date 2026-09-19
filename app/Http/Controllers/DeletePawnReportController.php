<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TDeletePawnSum;
use App\Models\TDeletePawnDetails;

class DeletePawnReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $branch_code = auth()->user()->BC;

        $recipts = TDeletePawnSum::whereBetween('Deleted_date', [$fromDate, $toDate])
                    ->where('BC', $branch_code)
                    ->get();

        return view('delete_pawn_report', compact('recipts'));
    }
}
