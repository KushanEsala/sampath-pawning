<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\summary; // Replace with your actual model

class SummaryController extends Controller
{
    public function summary(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $summary = summary::whereBetween('Date', [$fromDate, $toDate])->get();

        return view('summary', compact('summary'));
    }
}
