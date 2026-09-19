<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TPawnSum;
use App\Models\TRedeemSum;
use App\Models\Customer;
use App\Models\MPawnfeedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $branch_code = auth()->user()->BC;
        
        $query = TPawnSum::all()
                ->where('BC', $branch_code);

        $query2 = TRedeemSum::all()
                ->where('BC', $branch_code);
        
        $TotalPawn = $query->count();
        $TotalRedeem = $query2->count();
        $PawningTotal = $query->sum('Amount');
        $Pawningpayemt = number_format($PawningTotal, 2);

        $RedeemTotal = $query2->sum('Payable_Pawn_Amount');
        $Redeempayment = number_format($RedeemTotal, 2);

        $InterestTotal = $query->sum('Interest');
        $Interest = number_format($InterestTotal, 2);

        //Showing Pawning Customer Details in the Home page
        $Pawningdetails = TPawnSum::latest()
        ->where('BC',$branch_code)
        ->paginate(6);

        //Showing Pawning Customer Details in the Home page
        $Redeemdetails = TRedeemSum::latest()
        ->where('BC',$branch_code)
        ->paginate(6);

        $companyData = DB::table('customers')->select('NIC')->get();



        $pawnData = DB::table('t_pawn_sums')
            ->join('branch_dels', 't_pawn_sums.BC', '=', 'branch_dels.bccode')
            ->select(
                't_pawn_sums.BC',
                'branch_dels.name',
                DB::raw('SUM(CASE WHEN t_pawn_sums.IsRedeemed = 1 THEN t_pawn_sums.Amount ELSE 0 END) as redeemed_amount'),
                DB::raw('COUNT(CASE WHEN t_pawn_sums.IsRedeemed = 1 THEN 1 ELSE NULL END) as redeemed_count'),
                DB::raw('SUM(CASE WHEN t_pawn_sums.IsRedeemed = 0 THEN t_pawn_sums.Amount ELSE 0 END) as not_redeemed_amount'),
                DB::raw('COUNT(CASE WHEN t_pawn_sums.IsRedeemed = 0 THEN 1 ELSE NULL END) as not_redeemed_count'),
                DB::raw('COUNT(*) as total_records')
            )
            ->groupBy('t_pawn_sums.BC', 'branch_dels.name')
            ->get();



            $RedeemData = DB::table('t_redeem_sums')
            ->join('branch_dels', 't_redeem_sums.BC', '=', 'branch_dels.bccode')
            ->select(
                't_redeem_sums.BC',
                'branch_dels.name',
                DB::raw('SUM(t_redeem_sums.Original_Pawn_Amount) as total_redeem_amount'),
                DB::raw('SUM(t_redeem_sums.Paid_Interest) as total_Paid_Interest'),
                DB::raw('COUNT(*) as total_records')
            )
            ->groupBy('t_redeem_sums.BC', 'branch_dels.name')
            ->get();    

        return view('home', compact('TotalPawn','pawnData','RedeemData','companyData','TotalRedeem','Pawningpayemt','Redeempayment','Interest'))
        //Showing Customer Details in the Home page-23
        ->with('customerdetails',$Pawningdetails)

        //Showing Redeem Customer Details in the Home page-23
        ->with('RedeemCustomer',$Redeemdetails);
    }
    
    
    
    
public function getCustomerData(Request $request)
{
     $nic = $request->input('nic');
    $branch_code = auth()->user()->BC;

    $customers = TPawnSum::where('Receipt_Number', $nic)
                         ->where('BC', $branch_code)
                         ->get();
                         

    if ($customers->isNotEmpty()) {
        return response()->json($customers); // Return all records
    } else {
        return response()->json(['error' => 'Customer not found'], 404);
    }
}

public function FeedbackData(Request $request)
{
    $request->validate([
        'Customer_NIC' => 'required|string',
        'feedback' => 'required', // Adjust range as needed
    ]);

    $nic = $request->input('Customer_NIC');
    $feedback = $request->input('feedback');
    $customerName = $request->Customer_Name;
    $Customer_Code = $request->Customer_Code;
    $Receipt_Date = $request->Receipt_Date;

        $TPawnTrans = new MPawnfeedback; 
        $TPawnTrans->Receipt_Number = $request->Customer_NIC;
        $TPawnTrans->Customer_NIC = $Customer_Code; 
        $TPawnTrans->Customer_Name = $customerName;
        $TPawnTrans->Receipt_Date = $Receipt_Date; 
        $TPawnTrans->Current_date = Carbon::today(); // Or now()->toDateString()
        $TPawnTrans->feedback = $request->feedback; // Corrected line
        $TPawnTrans->OC = auth()->user()->username; 
        $TPawnTrans->BC = auth()->user()->BC;
        $TPawnTrans->save();
        
        
         $branch_code = auth()->user()->BC;

    $updated = TPawnSum::where('Receipt_Number', $nic)
    ->where('BC', $branch_code)
                ->update(['feedback' => $feedback]);

    if ($updated) {
        return response()->json(['message' => 'Customer Customer Updated Successfully.'], 200);
    } else {
        return response()->json(['message' => 'No matching record found.'], 404);
    }
}

public function PawnDataView(Request $request)
    {
       return View('pawningalldata');
    }



}