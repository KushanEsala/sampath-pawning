<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Http\Request;
use App\Models\itemCondition;
use App\Models\karatage;
use App\Models\Recei_Add;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Company;
use App\Models\TPawnDetails;
use App\Models\TPawnSum;
use App\Models\TRedeemSum;
use App\Models\TPawnPayment;
use App\Models\TOpeningPawnSum;
use App\Models\TOpeningPawnDetails;
use App\Models\branchDel;

class PawningPartPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch_code = auth()->user()->BC;

        $maxRedeemNo = TPawnPayment::where('BC',$branch_code)
        ->orderBy('Redeem_Number', 'desc')
        ->value('Redeem_Number');

        $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);
        $branchData = branchDel::all();

        return view('pawningPartPayment')
        ->with("branch",  $branchData)
        ->with("maxRedeem", $maxRedeemNos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function PartpaymentSearch(Request $request)
    {
        $receiptNo = $request->search_receipt_no;
        $branchReceipt = $request->branch;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where('Receipt_Number',$receiptNo)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->where('BC', $branchReceipt)
                        ->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->get();

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = Recei_Add::where('receiptname', $receipt_typ)->get();

            $maxRedeemNo = TPawnPayment::where('BC',$branch_code)
            ->orderBy('Redeem_Number', 'desc')
            ->value('Redeem_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            return view('searchPartPayment')
            ->with("maxRedeem", $maxRedeemNos)
            ->with('customerData', $cus_data)
            ->with('receiptTypeData', $receipt_data)
            ->with('pawnType', $pawn_type)
            ->with('receiptData', $data);
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function AddPartPayment(Request $request)
    {
        $NewRedeem = new TPawnPayment;
        $NewRedeem->Receipt_Number = $request->receipt_number;
        $NewRedeem->Invoice_Number = $request->invoice_number;
        $NewRedeem->Ticket_Number = $request->ticket_number;
    
        $NewRedeem->Pawn_Receipt_Type = $request->pawn_receipt_type;
        $NewRedeem->Redeem_Date = $request->redeem_date;
        $NewRedeem->Redeem_Number = $request->redeem_no;
        $NewRedeem->Total_Weight = $request->sum_total_weight;
        $NewRedeem->Pawn_Weight = $request->sum_pawn_weight;
        $NewRedeem->Original_Pawn_Amount = $request->original_pawn_amount;
        $NewRedeem->Payable_Pawn_Amount = $request->original_pawn_amount;
        $NewRedeem->Paid_Interest = $request->paid_interest;
        $NewRedeem->Payable_Interest = $request->payable_interest;
        $NewRedeem->Stamp_Fee = $request->stamp_fee;
        $NewRedeem->Document_Charges = $request->document_charges;
        $NewRedeem->Advance_Balance = $request->advance_balance;
        $NewRedeem->Advance_Payment = $request->advance_payment;
        $NewRedeem->Discount = $request->redeem_discount;
        $NewRedeem->Payable_Total = $request->payable_total;
        $NewRedeem->OC = auth()->user()->username;
        $NewRedeem->BC = auth()->user()->BC;
        $NewRedeem->save();
    
        $branch_code = auth()->user()->BC;
        $receiptInput_no = $request->receipt_number;
        $Pawn_Receipt_Type = $request->pawn_receipt_type;
    
        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode', $branch_code)->paginate(1);
    
        $receipt_advanceAmount = $request->advance_payment;
        $receipt_Interest = $request->interest_Paid;
        $Balance = $request->BalanceInterest;

    
        $advancePayments = TPawnSum::where('Receipt_Number', $request->receipt_number)
                                    ->where('BC', $branch_code)
                                    ->get();
    
        $amounts = $advancePayments->pluck('Pawn_Amount'); // Extract all Amount values  
        $totalAmount = $amounts->sum(); // Sum all values in the collection

        $amountsinterest = $advancePayments->pluck('interest_Paid'); // Extract all Amount values 
        $totalAmountinterest = $amountsinterest->sum(); // Sum all values in the collection

        $BalanceInterest = $advancePayments->pluck('BalanceInterest'); // Extract all Amount values 
        $totalBalanceInterest = $BalanceInterest->sum(); // Sum all values in the collection


    
        $totalAdvancePayment = $totalAmount - $receipt_advanceAmount;
        $totalInterest = $receipt_Interest + $totalAmountinterest;
        $Interest = $Balance + $totalBalanceInterest;
    
        if ($Pawn_Receipt_Type == "Pawn") {
            TPawnSum::where('Receipt_Number', $request->receipt_number)
                    ->where('BC', $branch_code)
                    ->update([
                        'Pawn_Amount' => $totalAdvancePayment,
                        'Advance_Payment' => $receipt_advanceAmount,
                        'interest_Paid' => $totalInterest,
                        'BalanceInterest' => $Interest,
                    ]);
    

                    
                  
            $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                            ->where('BC', $branch_code)
                            ->get();
    
            $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                                ->where('BC', $branch_code)
                                ->get();
    
            // Generate the PDF content using a view
            $pdf = PDF::loadView('redeemReceiptPrint', [
                'pawnSumData' => $T_sumdata,
                'pawnDetailsData' => $T_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData,
            ]);
    
            // Save the PDF to a temporary file
            $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
            $pdf->save($pdfPath);
    
        } elseif ($Pawn_Receipt_Type == "Opening_Pawn") {
            TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                            ->where('BC', $branch_code)
                            ->update([
                                'IsRedeemed' => 1,
                            ]);
    
            $T_open_sumdata = TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                                ->where('BC', $branch_code)
                                ->get();
    
            $T_open_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                                    ->where('BC', $branch_code)
                                    ->get();
    
            // Generate the PDF content using a view
            $pdf = PDF::loadView('redeemReceiptPrint', [
                'pawnSumData' => $T_open_sumdata,
                'pawnDetailsData' => $T_open_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData,
            ]);
    
            // Save the PDF to a temporary file
            $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
            $pdf->save($pdfPath);
        }
    
        $pdfUrl = asset('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
    
        return back()
            ->with('done', 'The Redeem has been added')
            ->with("pdfLink", $pdfUrl);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}