<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cancel_Pawning; // Replace with your actual model

class Cancel_PawningController extends Controller
{
    public function Cancel(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $Cancel_Pawning = Cancel_Pawning::whereBetween('Date', [$fromDate, $toDate])->get();

        return view('Cancel_Pawning', compact('Cancel_Pawning'));
    }
}
