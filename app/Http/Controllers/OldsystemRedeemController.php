<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Http\Request;
use App\Models\itemCondition;
use App\Models\karatage;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Company;
use App\Models\TPawnDetails;
use App\Models\TPawnSum;
use App\Models\TRedeemSum;
use App\Models\TOpeningPawnSum;
use App\Models\TOpeningPawnDetails;
use App\Models\branchDel;
use App\Models\TCustomerAccount;
use App\Models\TPawnTrans;
use App\Models\MPawnfeedback;
use Illuminate\Support\Facades\DB;


class OldsystemRedeemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $branch_code = auth()->user()->BC;
        $maxRedeemNo = TRedeemSum::orderBy('Redeem_Number', 'desc')
        ->value('Redeem_Number');
        $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

        $branchData = branchDel::where('bccode',$branch_code)
                    ->paginate(1);

        return view('oldRedeemReceipt')
        ->with("branchDetails", $branchData)
        ->with("maxRedeem", $maxRedeemNos);
    }

    // Receipt Search function
 public function searchOld(Request $request)
    {
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where('old_Receipt_Number', $receiptNo)
                        ->where('BC', $branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->where('Bill_System_bill_type', 'OLD_SYSTEM')
                        ->get();

        $data = $dataTPawnSum;

        if ($data->count() > 0) {
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = TPawnSum::where('Customer_NIC', $cus_nic)->get();

            $maxRedeemNo = TRedeemSum::orderBy('Redeem_Number', 'desc')
                ->value('Redeem_Number');
            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            $MPawnfeedback = MPawnfeedback::where('Receipt_Number', $receiptNo)
                ->where('BC', $branch_code)
                ->get();

            return view('oldredeemSearch')
                ->with("maxRedeem", $maxRedeemNos)
                ->with('customerData', $cus_data)
                ->with('MPawnfeedback', $MPawnfeedback)
                ->with('pawnType', $pawn_type)
                ->with('receiptData', $data);
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    // Invoice Search function
    public function searchInvoice(Request $request)
    {
        $invoiceNo = $request->search_invoice_no;
        $branch_code = auth()->user()->BC;

        $TOpeningPawnSumdata = TOpeningPawnSum::where('Invoice_Number', $invoiceNo)
                        ->where('BC', $branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->get();

        $data = $TOpeningPawnSumdata;

        if ($data->count() > 0) {
            $branch_code = auth()->user()->BC;

            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC', $branch_code)
                        ->get();

            $maxRedeemNo = TRedeemSum::where('BC', $branch_code)
                            ->orderBy('Redeem_Number', 'desc')
                            ->value('Redeem_Number');
            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Opening_Pawn";

            return view('oldredeemSearch')
                ->with("maxRedeem", $maxRedeemNos)
                ->with('customerData', $cus_data)
                ->with('pawnType', $pawn_type)
                ->with('receiptData', $data);
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    // Ticket Search function
    public function searchTicket(Request $request)
    {
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where('Ticket_Number', $receiptNo)
                        ->where('BC', $branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->get();

        $data = $dataTPawnSum;

        if ($data->count() > 0) {
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC', $branch_code)
                        ->get();

            $maxRedeemNo = TRedeemSum::where('BC', $branch_code)
                ->orderBy('Redeem_Number', 'desc')
                ->value('Redeem_Number');
            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            return view('oldredeemSearch')
                ->with("maxRedeem", $maxRedeemNos)
                ->with('customerData', $cus_data)
                ->with('pawnType', $pawn_type)
                ->with('receiptData', $data);
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    public function create()
    {
        //
    }

    public function StoreOld(Request $request)
    {
        $request->validate(['receipt_number'=>'required', 'pawn_receipt_type'=>'required|in:Pawn,Opening_Pawn']);
        DB::beginTransaction();

        try {
            $activeReceipt = \App\Services\ReceiptPaymentEligibility::lock($request->pawn_receipt_type, $request->receipt_number, auth()->user()->BC);
            $NewRedeem = new TRedeemSum;
            $NewRedeem->Receipt_Number      = $request->receipt_number;
            $NewRedeem->Invoice_Number      = $request->invoice_number;
            $NewRedeem->Ticket_Number       = $request->ticket_number;
            $NewRedeem->Pawn_Receipt_Type   = $request->pawn_receipt_type;
            $NewRedeem->Redeem_Date         = $request->redeem_date;
            $NewRedeem->Redeem_Number       = $request->redeem_no;
            $NewRedeem->Total_Weight        = $request->sum_total_weight;
            $NewRedeem->Pawn_Weight         = $request->sum_pawn_weight;
            $NewRedeem->Original_Pawn_Amount = $request->original_pawn_amount;
            $NewRedeem->Payable_Pawn_Amount  = $request->original_pawn_amount;
            $NewRedeem->Paid_Interest       = $request->paid_interest;
            $NewRedeem->Payable_Interest    = $request->payable_interest;
            $NewRedeem->Stamp_Fee           = $request->stamp_fee;
            $NewRedeem->Document_Charges    = $request->document_charges;
            $NewRedeem->Advance_Balance     = $request->advance_balance;
            $NewRedeem->Discount            = $request->redeem_discount;
            $NewRedeem->Payable_Total       = $request->payable_total;
            $NewRedeem->Customer_Name       = $request->Customer_Name;
            $NewRedeem->Customer_NIC        = $request->Customer_NIC;
            $NewRedeem->Postage_Charges     = $request->Postage_Charges;
            $NewRedeem->old_Interest       = $request->old_interest;
            $NewRedeem->Stamp_Fee           = $request->stampduty;
            $NewRedeem->OC                  = auth()->user()->username;
            $NewRedeem->BC                  = auth()->user()->BC;
            $NewRedeem->save();

            $TPawnTrans = new TPawnTrans;
            $TPawnTrans->Customer_NIC  = $request->Customer_NIC;
            $TPawnTrans->Customer_Name = $request->Customer_Name;
            $TPawnTrans->code          = $request->receipt_number;
            $TPawnTrans->trans_no      = $request->redeem_no;
            $TPawnTrans->trans_type    = "REDEEM";
            $TPawnTrans->trans_amount  = $request->original_pawn_amount;
            $TPawnTrans->dDate         = $request->redeem_date;
            $TPawnTrans->Cr_amount     = 0;
            $TPawnTrans->Dr_amount     = $request->payable_total;
            $TPawnTrans->OC            = auth()->user()->username;
            $TPawnTrans->BC            = auth()->user()->BC;
            $TPawnTrans->save();

            $CustomerAccount = new TCustomerAccount();
            $CustomerAccount->customer_id              = $request->Customer_NIC;
            $CustomerAccount->Receipt_Date             = $request->redeem_date;
            $CustomerAccount->Receipt_Number           = $request->redeem_no;
            $CustomerAccount->Pawning_Number           = $request->receipt_number;
            $CustomerAccount->Payment_amount           = $request->payable_total;
            $CustomerAccount->Paid_Interest            = $request->paid_interest;
            $CustomerAccount->Balance_Interest_amount  = $request->BalanceInterest;
            $CustomerAccount->Paided_Captial_amount    = $request->advance_payment;
            $CustomerAccount->Balance_captial_amount   = $request->advance_payment;
            $CustomerAccount->trans_Type               = "REDEEM_PAYMENT";
            $CustomerAccount->OC                       = auth()->user()->username;
            $CustomerAccount->BC                       = auth()->user()->BC;
            $CustomerAccount->save();

            $branch_code        = auth()->user()->BC;
            $receiptInput_no    = $request->receipt_number;
            $Pawn_Receipt_Type  = $request->pawn_receipt_type;
            $Payable_Interest   = $request->paid_interest;
            $Redeem_Date        = $request->redeem_date;

            $cashAccountId          = 2;
            $pawnLoansAccountId     = 1;
            $interestIncomeAccountId = 3;
            $feeIncomeAccountId     = 4;

            $totalFees = ($request->stampduty ?? 0) +
                         ($request->document_charges ?? 0) +
                         ($request->Postage_Charges ?? 0);

            $discount = $request->redeem_discount ?? 0;

            // Debit: Cash Account
            DB::table('t_account_trans')->insert([
                'trans_date'   => $request->redeem_date,
                'voucher_no'   => 'RDM-' . $request->invoice_number,
                'account_id'   => $cashAccountId,
                'related_id'   => $request->redeem_no,
                'related_type' => 'REDEEM',
                'dr'           => $request->payable_total,
                'Invoice_no'   => $request->invoice_number,
                'cr'           => 0,
                'description'  => 'Cash received for redemption of pawn receipt #' . $request->invoice_number . ' - ' . $request->Customer_Name,
                'branch_code'  => $branch_code,
                'created_by'   => auth()->user()->username,
                'created_at'   => now(),
                'updated_at'   => now()
            ]);

            // Discount entry
            if ($discount > 0) {
                $discountExpenseAccountId = 5;
                DB::table('t_account_trans')->insert([
                    'trans_date'   => $request->redeem_date,
                    'voucher_no'   => 'RDM-' . $request->redeem_no,
                    'account_id'   => $discountExpenseAccountId,
                    'related_id'   => $request->redeem_no,
                    'Invoice_no'   => $request->invoice_number,
                    'related_type' => 'REDEEM',
                    'dr'           => $discount,
                    'cr'           => 0,
                    'description'  => 'Discount allowed on redemption - Receipt #' . $request->receipt_number,
                    'branch_code'  => $branch_code,
                    'created_by'   => auth()->user()->username,
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            }

            $companyData = Company::latest()->paginate(1);
            $branchData  = branchDel::where('bccode', $branch_code)->paginate(1);

            if ($Pawn_Receipt_Type == "Pawn") {
                TPawnSum::where('Receipt_Number', $request->receipt_number)
                    ->where('BC', $branch_code)
                    ->update([
                        'IsRedeemed'      => 1,
                        'Redeem_interest' => $Payable_Interest,
                        'Redeem_Date'     => $Redeem_Date,
                    ]);

                $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->get();

                $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->get();

                $T_redeemdata = TRedeemSum::where('Redeem_Number', $request->redeem_no)
                    ->where('Pawn_Receipt_Type', $request->pawn_receipt_type)
                    ->where('BC', $branch_code)
                    ->get();

                $pdf = PDF::loadView('oldredeemReceiptPrint', [
                    'redeemdata'       => $T_redeemdata,
                    'pawnSumData'      => $T_sumdata,
                    'pawnDetailsData'  => $T_detailsdata,
                    'branchDetails'    => $branchData,
                    'companyData'      => $companyData
                ]);

                $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
                $pdf->save($pdfPath);

            } elseif ($Pawn_Receipt_Type == "Opening_Pawn") {

                TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->update(['IsRedeemed' => 1]);

                $T_open_sumdata = TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->get();

                $T_open_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->get();

                $pdf = PDF::loadView('oldredeemReceiptPrint', [
                    'pawnSumData'     => $T_open_sumdata,
                    'pawnDetailsData' => $T_open_detailsdata,
                    'branchDetails'   => $branchData,
                    'companyData'     => $companyData
                ]);

                $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
                $pdf->save($pdfPath);
            }

            $pdfUrl = asset('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');

            \App\Services\ReceiptArticleStatus::sync($activeReceipt->fresh(), $request->pawn_receipt_type);
            DB::commit();

            return back()
                ->with('done', 'The Redeem has been added')
                ->with("pdfLink", $pdfUrl);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'An error occurred: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function getArticleDetailsold(Request $request){
        $receiptNo   = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;
        if ($request->boolean('payment_workflow') && !\App\Services\ReceiptPaymentEligibility::query('Pawn', $branch_code)->where('Receipt_Number', $receiptNo)->exists()) {
            return response()->json(['status'=>'not_found', 'data'=>[]]);
        }

        $data = TPawnDetails::where('Receipt_Number', $receiptNo)
                            ->where('BC', $branch_code)
                            ->get();

        if($data->count() != null){
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }


    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

   public function OldPawnReport(Request $request){
    // Get search criteria from the form input
    $branch_code = auth()->user()->BC;
    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');

    $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                ->where('BC', $branch_code)
                ->where('Bill_System_bill_type', 'OLD_SYSTEM')
                ->get();

    $fromDate  = $request->input('from_date');
    $toDate = $request->input('to_date');

    // Construct a query to filter records based on search criteria
    $query = TPawnSum::query();
    if ($fromDate && $toDate) {
        $query = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
                    ->where('BC', $branch_code)
                    ->where('Bill_System_bill_type', 'OLD_SYSTEM')
                    ->get();
    }

    // Calculate the sum of the desired column (e.g., 'amount_column')
    $total_weight = $query->sum('Total_Weight');
    $totalWeight = number_format($total_weight, 2);

    $pawn_weight = $query->sum('Pawn_Weight');
    $pawnWeight = number_format($pawn_weight, 2);

    $total = $query->sum('Amount');
    $totalAmount = number_format($total, 2);
    $int =  $query->sum('Interest');
    $interest = number_format($int, 2);
    $TotalPawn = TPawnSum::count();

    return view('old_search_results')
    ->with("recipts", $query)
    -> with("recipts", $query)
    -> with("totalAmount", $totalAmount)
    -> with("totalWeight", $totalWeight)
    -> with("pawnWeight", $pawnWeight)
    -> with("Interest", $interest);
    }
}
