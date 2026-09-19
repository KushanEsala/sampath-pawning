<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Recei_Add;
use App\Models\TPawnSum;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RedeemLateLettersController extends Controller
{
//   public function index(Request $request)
// {
//     $receiptType = Recei_Add::all();

//     $branch_code = auth()->user()->BC;
//     $currentDateTime = now(); 
//     $receipt_type = $request->input('receipt_type'); // example: 1, 3, 6

//     $receiptDate = $currentDateTime->addMonths($receipt_type);

//     // Convert the calculated Receipt_Date to a string (if you need to format it)
//     $formattedReceiptDate = $receiptDate->format('Y-m-d'); // e.g., 2025-01-10

//         // Example for different $receipt_type values:
//         if ($receipt_type == 1) {
//             $receiptDate = $currentDateTime->addMonths(1);
//         } elseif ($receipt_type == 3) {
//             $receiptDate = $currentDateTime->addMonths(3);
//         } elseif ($receipt_type == 6) {
//             $receiptDate = $currentDateTime->addMonths(6);
//         }

//     $oldPawns = TPawnSum::whereDate('Receipt_Date', '<', $currentDateTime)
//         ->where('IsRedeemed', 0)
//         ->where('BC', $branch_code)
//         ->paginate(10);

//     $companyData = Company::latest()->paginate(1);

//     return view('redeem_late_letter')
//         ->with("receiptType", $receiptType)
//         ->with("companyData", $companyData)
//         ->with("recipts", $oldPawns)
//         ->with("formattedReceiptDate", $formattedReceiptDate);  // Pass the calculated receipt date
// }

    public function index(Request $request){
        $branch_code =auth()->user()->BC;
        $currentDateTime = now();
        $receipt_type = $request->receipt_type;

        $oldPawns = TPawnSum::whereDate('To_Date', '<', $currentDateTime)
                    ->where('IsRedeemed', 0)
                    ->where('Receipt_Type', $receipt_type)
                    ->where('BC', $branch_code)
                    ->paginate(10);

        $companyData = Company::latest()->paginate(1);
        $receiptType = Recei_Add::all();

        return view('redeem_late_letter')
            ->with("receiptType", $receiptType)
            ->with("companyData" , $companyData)
            ->with("recipts" , $oldPawns);
    }



    public function printIndex(Request $request){

        $branch_code =auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        // Get the current date and time
        $currentDateTime = now();
        $oldPawns = TPawnSum::whereDate('To_Date', '<', $currentDateTime)
                    ->where('IsRedeemed', 0)
                    ->where('BC', $branch_code)
                    ->paginate(10);

        if ($fromDate && $toDate) {
            $oldPawns = TPawnSum::whereBetween('letter_3_date', [$fromDate, $toDate])
                        ->orWhereBetween('letter_2_date', [$fromDate, $toDate])
                        ->orWhereBetween('letter_1_date', [$fromDate, $toDate])
                        ->where('IsRedeemed', 0)
                        ->where('BC', $branch_code)
                        ->paginate(10);
        }

        return view('late_redeem_print_list')
        ->with("toDate" , $toDate)
        ->with("fromDate" , $fromDate)
        ->with("recipts" , $oldPawns);
    }


    public function print(Request $request){
        $currentDateTime = now();
        $receipt_no = $request->print_recept_no;
        $letter_no = $request->letter_no;

        if($letter_no == 3){
            TPawnSum::where('Receipt_Number',$receipt_no)->update([
                'is_letter_3'=>1,
                'letter_3_date'=>$currentDateTime,
            ]);
        }
        elseif($letter_no == 2){
            TPawnSum::where('Receipt_Number',$receipt_no)->update([
                'is_letter_2'=>1,
                'letter_2_date'=>$currentDateTime,
            ]);
        }
        else{
            TPawnSum::where('Receipt_Number',$receipt_no)->update([
                'is_letter_1'=>1,
                'letter_1_date'=>$currentDateTime,
            ]);
        }

        return response()->json([
            'status'=>'success',
        ]);
    }
    
        public function LateRedeem(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $currentDateTime = now();
        $receipt_type = $request->receipt_type;
    
        // Fetch old pawns based on conditions
        $oldPawns = TPawnSum::whereDate('To_Date', '<', $currentDateTime)
            ->where('IsRedeemed', 0)
            ->where('Receipt_Type', $receipt_type)
            ->where('BC', $branch_code)
            ->paginate(10);
    
        // Fetch company data and receipt types
        $companyData = Company::latest()->paginate(1);
        $receiptType = Recei_Add::all();
    
        // Return the view with data
        return view('redeem_late_letter', [
            'receiptType' => $receiptType,
            'companyData' => $companyData,
            'recipts' => $oldPawns,
        ]);
    }
    
    
    
    


}