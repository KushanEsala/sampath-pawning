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
use App\Models\TOpeningPawnSum;
use App\Models\TOpeningPawnDetails;
use App\Models\branchDel;

class RedeemController extends Controller
{

    public function index()
    {
        $branch_code = auth()->user()->BC;
        $maxRedeemNo = TRedeemSum::where('BC',$branch_code)
        ->orderBy('Redeem_Number', 'desc')
        ->value('Redeem_Number');
        $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

        $branchData = branchDel::where('bccode',$branch_code)
                    ->paginate(1);

        return view('RedeemReceipt')
        ->with("branchDetails", $branchData)
        ->with("maxRedeem", $maxRedeemNos);
    }

    //receipt Search function
    public function search(Request $request)
    {
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where('Receipt_Number',$receiptNo)
                        ->where('BC',$branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->get();

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = Recei_Add::where('receiptname', $receipt_typ)->get();

            $maxRedeemNo = TRedeemSum::where('BC',$branch_code)
            ->orderBy('Redeem_Number', 'desc')
            ->value('Redeem_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            return view('redeemSearch')
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

    //invoice Search function
    public function searchInvoice(Request $request)
    {
        $invoiceNo = $request->search_invoice_no;
        $branch_code = auth()->user()->BC;

        $TOpeningPawnSumdata = TOpeningPawnSum::where('Invoice_Number',$invoiceNo)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->get();

        $data = $TOpeningPawnSumdata;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;

            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC',$branch_code)
                        ->get();

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = Recei_Add::where('receiptname', $receipt_typ)->get();

            $maxRedeemNo = TRedeemSum::where('BC',$branch_code)
                            ->orderBy('Redeem_Number', 'desc')
                            ->value('Redeem_Number');
            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Opening_Pawn";

            return view('redeemSearch')
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

    //invoice searchTicket function
    public function searchTicket(Request $request)
    {
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where('Ticket_Number',$receiptNo)
                        ->where('BC',$branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                        ->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC',$branch_code)
                        ->get();

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = Recei_Add::where('receiptname', $receipt_typ)->get();

            $maxRedeemNo = TRedeemSum::where('BC',$branch_code)
            ->orderBy('Redeem_Number', 'desc')
            ->value('Redeem_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            return view('redeemSearch')
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

    public function create()
    {

    }


    public function store(Request $request)
    {
        $NewRedeem = new TRedeemSum;
        $NewRedeem->Receipt_Number = $request->receipt_number;
        $NewRedeem->Invoice_Number = $request->invoice_number;
        $NewRedeem->Ticket_Number = $request->ticket_number;
        $NewRedeem->Customer_Name = $request->Customer_Name;
        $NewRedeem->Customer_NIC = $request->Customer_NIC;
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
        $NewRedeem->Discount = $request->redeem_discount;
        $NewRedeem->Payable_Total = $request->payable_total;
        $NewRedeem->OC = auth()->user()->username;
        $NewRedeem->BC = auth()->user()->BC;
        $NewRedeem->save();

        $branch_code = auth()->user()->BC;
        $receiptInput_no = $request->receipt_number;
        $Pawn_Receipt_Type = $request->pawn_receipt_type;
        $Payable_Interest = $request->paid_interest;

        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode',$branch_code)->paginate(1);

        if($Pawn_Receipt_Type == "Pawn"){
            TPawnSum::where('Receipt_Number',$request->receipt_number)
                    ->where('BC',$branch_code)
                    ->update([
                        'IsRedeemed'=> 1,
                    ]);

            $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                            ->where('BC',$branch_code)
                            ->get();

            $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                            ->where('BC',$branch_code)
                            ->get();
                            
                                                             
            $T_redeemdata  = TRedeemSum::where('Redeem_Number', $request->redeem_no)
                            ->where('Pawn_Receipt_Type',$request->pawn_receipt_type)
                            ->where('BC',$branch_code)
                            ->get();  

            // Generate the PDF content using a view
            $pdf = PDF::loadView('redeemReceiptPrint', [
                'redeemdata' => $T_redeemdata,
                'pawnSumData' => $T_sumdata ,
                'pawnDetailsData' => $T_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData
            ]);

            // Save the PDF to a temporary file
            $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt'.$branch_code.'.pdf');
            $pdf->save($pdfPath);

        }elseif($Pawn_Receipt_Type == "Opening_Pawn"){

            TOpeningPawnSum::where('Receipt_Number',$receiptInput_no)
                            ->where('BC',$branch_code)
                            ->update([
                                'IsRedeemed'=> 1,
                                'Redeem_interest'=> $Payable_Interest,
                            ]);

            $T_open_sumdata = TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                                ->where('BC',$branch_code)
                                ->get();

            $T_open_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                                    ->where('BC',$branch_code)
                                    ->get();
                                    
            $T_redeemdata  = TRedeemSum::where('Redeem_Number', $request->redeem_no)
                            ->where('Pawn_Receipt_Type',$request->pawn_receipt_type)
                            ->where('BC',$branch_code)
                            ->get();                             

            // Generate the PDF content using a view
            $pdf = PDF::loadView('redeemReceiptPrint',[
                'redeemdata' => $T_redeemdata,
                'pawnSumData' => $T_open_sumdata ,
                'pawnDetailsData' => $T_open_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData
            ]);

            // Save the PDF to a temporary file
            $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt'.$branch_code.'.pdf');
            $pdf->save($pdfPath);
        }


        // //update column to Redeemed
        // $receiptInput_no = $request->receipt_number;
        // TPawnSum::where('Receipt_Number',$request->receipt_number)
        //         ->where('BC',$branch_code)
        //         ->update(['IsRedeemed'=> 1,]);

        // $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
        //                 ->where('BC',$branch_code)
        //                 ->get();

        // $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
        //             ->where('BC',$branch_code)
        //             ->get();

        // // Generate the PDF content using a view
        // $pdf = PDF::loadView('redeemReceiptPrint', [
        //     'pawnSumData' => $T_sumdata ,
        //     'pawnDetailsData' => $T_detailsdata,
        //     'companyData' => $companyData
        // ]);

        // // Save the PDF to a temporary file
        // $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt'.$branch_code.'.pdf');
        // $pdf->save($pdfPath);

        // if($T_sumdata==null){
        //     $branch_code = auth()->user()->BC;

        //     //update column to Redeemed
        //     $invoiceInput_no = $request->invoice_number;
        //     TOpeningPawnSum::where('Invoice_Number',$request->invoice_number)
        //         ->where('BC',$branch_code)
        //         ->update(['IsRedeemed'=> 1,]);

        //     $T_open_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $invoiceInput_no)
        //                         ->where('BC',$branch_code)
        //                         ->get();

        //     $T_open_sumdata = TOpeningPawnSum::where('Receipt_Number', $invoiceInput_no)
        //                     ->where('BC',$branch_code)
        //                     ->get();

        //     // Generate the PDF content using a view
        //     $pdf = PDF::loadView('redeemReceiptPrint', [
        //         'pawnSumData' => $T_open_sumdata ,
        //         'pawnDetailsData' => $T_open_detailsdata,
        //         'companyData' => $companyData
        //     ]);

        //     // Save the PDF to a temporary file
        //     $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt'.$branch_code.'.pdf');
        //     $pdf->save($pdfPath);
        // }


        $pdfUrl = asset('../public/assets/pdf/Redeem_receipt'.$branch_code.'.pdf');

        return back()
        ->with('done','The Redeem has been added')
        ->with("pdfLink", $pdfUrl);
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}