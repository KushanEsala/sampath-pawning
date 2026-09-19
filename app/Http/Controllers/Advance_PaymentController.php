<?php

namespace App\Http\Controllers;
use App\Models\Advance_Payment; // Replace with your actual model


use Illuminate\Http\Request;

class Advance_PaymentController extends Controller


{
    public function advance(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $Advance_Payments = Advance_Payment::whereBetween('Paid_Date_Time', [$fromDate, $toDate])->get();

        return view('Advance_Payment', compact('Advance_Payments'));
    }
}
