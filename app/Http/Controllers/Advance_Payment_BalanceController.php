<?php

namespace App\Http\Controllers;
use App\Models\Advance_Payment_Balance; // Replace with your actual model


use Illuminate\Http\Request;

class Advance_Payment_BalanceController extends Controller


{
    public function balance(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $Advance_balance = Advance_Payment_Balance::whereBetween('Date_Time', [$fromDate, $toDate])->get();

        return view('Advance_Payment_Balance', compact('Advance_balance'));
    }
}
