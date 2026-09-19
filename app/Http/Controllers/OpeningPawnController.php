<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\itemCondition;
use App\Models\karatage;
use App\Models\Recei_Add;
use App\Models\Category;
use App\Models\Customer;
use App\Models\TPawnDetails;
use App\Models\TPawnSum;
use App\Models\itemSetup;
use App\Models\TOpeningPawnSum;
use App\Models\TOpeningPawnDetails;
use App\Models\Company;
use App\Models\branchDel;
use App\Models\TDeletePawnSum;
use App\Models\TDeletePawnDetails;

class OpeningPawnController extends Controller
{

    public function index()
    {
        $itemCondition = itemCondition::all();
        $itemSetup = itemSetup::all();
        $karatageData = karatage::all();
        $receiptType = Recei_Add::all();
        $itemCategory = Category::all();
        $branchData = branchDel::latest()->paginate(1);

        $branch_code = auth()->user()->BC;
        $maxReceiptNo = TOpeningPawnSum::where('BC', $branch_code)
                        ->orderBy('Receipt_Number', 'desc')
                        ->value('Receipt_Number');

        $maxReceiptNos = str_pad($maxReceiptNo, 4, '0', STR_PAD_LEFT);

        // get customer code max
        $maxCustomerCode = Customer::orderBy('Code', 'desc')->value('Code');
        $maxCustomerCodes = str_pad($maxCustomerCode, 4, '0', STR_PAD_LEFT);

        // $maxReceiptNo = Receipt::max('Receipt_Number');
        return view('OpeningPawnReceipt')
        ->with("itemCondition" , $itemCondition)
        ->with("itemSetup" , $itemSetup)
        ->with("receiptType" , $receiptType)
        ->with("itemCategory" , $itemCategory)
        ->with("itemKaratage", $karatageData)
        ->with("maxCustomer", $maxCustomerCodes)
        ->with("branchDetails", $branchData)
        ->with("maxReceipt", $maxReceiptNos);
    }


    public function storePawnSum(Request $request){
        $request->validate([
            'customer_nic' => ' required',
            'customer_name' => ' required',
            'customer_address' => ' required',
            'customer_contact_1' => ' required',
            'receipt_type' => ' required',
            // 'receipt_no' => ' required | unique:t_opening_pawn_sums,Receipt_Number',
            // 'invoice_no' => 'required | unique:t_opening_pawn_sums,Invoice_Number',
            'receipt_date' => ' required',
            'amount' => ' required',
            'total_amount' => ' required',
            // 'oc' => ' required',
            // 'bc' => ' required'
            'inputs.*.category' => ' required',
            'inputs.*.articles' => ' required',
            'inputs.*.condition' => ' required',
            'inputs.*.karatage' => ' required',
            'inputs.*.weight' => ' required',
            'inputs.*.qty' => ' required',
            'inputs.*.value' => ' required'
        ],[
            'customer_nic' => ' The NIC field is required',
            'customer_name' => ' The Name field is required',
            'customer_address' => ' The Address field is required',
            'customer_contact_1' => ' The Contact field is required',
            'receipt_type' => ' The Receipt type field is required',
            'receipt_no' => ' The Receipt No has already been taken',
            'receipt_date' => ' The Date field is required',
            'amount' => ' The Amount field is required',
            'total_amount' => 'The Total Amount field is required',
            // 'oc' => ' The Operator field is required',
            // 'bc' => ' The Branch field is required'
        ]);

        $Receipt_Type = $request->receipt_type;
        $Receipt_Date = $request->receipt_date;
        $receipt_data = Recei_Add::where('receiptname', $Receipt_Type)->get();
        foreach ($receipt_data as $receipt) {
            $validPeriod = $receipt->validPeriod;
        }
        $to_date = Carbon::parse($Receipt_Date)->addDays($validPeriod)->toDateString();

        $PawnSum = new TOpeningPawnSum;
        $PawnSum->Customer_NIC = $request->customer_nic;
        $PawnSum->Customer_Name = $request->customer_name;
        $PawnSum->First_name = $request->first_name;
        $PawnSum->Middle_name = $request->middle_name;
        $PawnSum->Last_name = $request->last_name;
        $PawnSum->Customer_Address = $request->customer_address;
        $PawnSum->Customer_Phone = $request->customer_contact_1;
        $PawnSum->Receipt_Type = $request->receipt_type;
        $PawnSum->Valid_Period = $request->valid_period;
        $PawnSum->Receipt_Number = $request->receipt_no;
        $PawnSum->Invoice_Number = $request->invoice_no;
           $PawnSum->Stock_No = $request->Stock_No;
        $PawnSum->Receipt_Date = $request->receipt_date;
        $PawnSum->To_Date = $to_date;
        $PawnSum->Total_Weight = $request->sum_total_weight;
        $PawnSum->Pawn_Weight = $request->sum_pawn_weight;
        $PawnSum->Amount = $request->amount;
        $PawnSum->Pawn_Amount = $request->amount;
        $PawnSum->Total_Amount = $request->total_amount;
        $PawnSum->Interest = $request->interest;
        $PawnSum->IsRedeemed = 0;
        $PawnSum->isForfeit = 0;
        $PawnSum->Trans_Code ='OP_STOCK';
        $PawnSum->OC = auth()->user()->username;
        $PawnSum->BC = auth()->user()->BC;
        $PawnSum->save();

        foreach ($request -> inputs as $key=>$value){
        $PawnDetails = new TOpeningPawnDetails;
        $PawnDetails->Receipt_Number=$value['receipt_no'];
        $PawnDetails->Invoice_Number=$value['invoice_no'];
        $PawnDetails->Receipt_Type=$value['receipt_type'];
        $PawnDetails->Date=$value['receipt_date'];
        $PawnDetails->Category=$value['category'];
        $PawnDetails->Articles=$value['articles'];
        $PawnDetails->Condition=$value['condition'];
        $PawnDetails->Karatage=$value['karatage'];
        $PawnDetails->Weight=$value['weight'];
        $PawnDetails->Total_Weight=$value['total_weight'];
        $PawnDetails->QTY=$value['qty'];
        $PawnDetails->Value=$value['value'];
        $PawnDetails->Trans_Code ='OP_STOCK';
        $PawnDetails->IsRedeemed ='0';
        $PawnDetails->isForfeit ='0';
        $PawnDetails->OC = auth()->user()->username;
        $PawnDetails->BC = auth()->user()->BC;
        $PawnDetails->save();
        }

        $receiptInput_no = $request->receipt_no;
        $branch_code = auth()->user()->BC;

        $T_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                        ->where('BC',$branch_code)
                        ->get();

        $T_sumdata = TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC',$branch_code)
                    ->get();

        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode', $branch_code)->get();

        // Generate the PDF content using a view
        $pdf = PDF::loadView('pawnReceiptPrint', [
            'pawnSumData' => $T_sumdata ,
            'pawnDetailsData' => $T_detailsdata,
            'companyData' => $companyData,
            'branchDetails'=> $branchData
        ]);

        // Save the PDF to a temporary file
        $pdfPath = storage_path('../public/assets/pdf/Opening_pawn_receipt'.$branch_code.'.pdf');
        $pdf->save($pdfPath);

        $pdfUrl = asset('public/assets/pdf/Opening_pawn_receipt'.$branch_code.'.pdf');

        // Return the PDF as a download
        // return $pdf->stream();

        sleep(1);
        return back()
        ->with('done','The receipt has been added')
        ->with("pdfLink", $pdfUrl);

        // return $pdf->download('report.pdf');

    }


    public function printReceipt(Request $request){
        $receiptInput_no = $request->receipt_no;
        $branch_code = auth()->user()->BC;

        $T_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                        ->where('BC',$branch_code)
                        ->get();

        $T_sumdata = TOpeningPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC',$branch_code)
                    ->get();

        // Generate the PDF content using a view
        $pdf = PDF::loadView('pawnReceiptPrint', [
            'pawnSumData' => $T_sumdata ,
            'pawnDetailsData' => $T_detailsdata
        ]);

        // Save the PDF to a temporary file
        // $pdfPath = storage_path('app/temp/pawn_receipt.pdf');
        $pdfPath = storage_path('../public/assets/pdf/pawn_receipt.pdf');
        $pdf->save($pdfPath);

        // $pdfUrl = Storage::url('app/temp/pawn_receipt.pdf');
        $pdfUrl = asset('assets/pdf/pawn_receipt.pdf');
        // return $pdf->stream();
        // return $pdfPath;
        return response()->json([
            'status' => 'success',
            'pdf_url' => $pdfUrl
        ]);
    }


    // get customer using receipt number
    public function getCustomerReceiptNo(Request $request){
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $data = TOpeningPawnSum::where('Receipt_Number',$receiptNo)
                ->where('BC',$branch_code)
                ->get();

        if($data->count() != null){
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
            return view('pawning_search_customer_using_receipt_no')->with("customer_get", $data)->render();
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }


    public function search(Request $request){
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $data = TOpeningPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('BC', $branch_code)
                ->get();

        $itemCondition = itemCondition::all();
        $itemSetup = itemSetup::all();
        $karatageData = karatage::all();
        $receiptType = Recei_Add::all();
        $itemCategory = Category::all();

        if($data->count() != null){
            return view('pawning_get_receipt')
            ->with("itemCondition" , $itemCondition)
            ->with("itemSetup" , $itemSetup)
            ->with("itemKaratage", $karatageData)
            ->with("receiptType" , $receiptType)
            ->with("itemCategory" , $itemCategory)
            ->with('receiptData', $data);
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    public function getArticleDetails(Request $request){
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $data = TOpeningPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('BC', $branch_code)
                ->get();

        if($data->count() != null){
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);

        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    //delete pawn receipt
    public function  deleteReceipt(Request $request){
        $receiptNo = $request->receipt_no;
        $receiptType = $request->receipt_type;
        $userName = $request->user_name;
        $reason = $request->reason;
        $op = $request->op;
        $branch_code = auth()->user()->BC;

        $records1 = TOpeningPawnSum::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC', $branch_code)
                ->get();

        $records2 = TOpeningPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC', $branch_code)
                ->get();

        if($records1 && $records2){

            foreach ($records1 as $data1) {
                $DeletePawnSum = new TDeletePawnSum;
                $DeletePawnSum->Customer_NIC = $data1->Customer_NIC;
                $DeletePawnSum->Customer_Name = $data1->Customer_Name;
                $DeletePawnSum->Customer_Address = $data1->Customer_Address;
                $DeletePawnSum->Customer_Phone = $data1->Customer_Phone;
                $DeletePawnSum->Receipt_Type = $data1->Receipt_Type;
                $DeletePawnSum->Pawn_Type = "OP";
                $DeletePawnSum->Valid_Period = $data1->Valid_Period;
                $DeletePawnSum->Receipt_Number = $data1->Receipt_Number;
                $DeletePawnSum->Invoice_Number = $data1->Invoice_Number;
                $DeletePawnSum->Receipt_Date = $data1->Receipt_Date;
                $DeletePawnSum->Total_Weight = $data1->Total_Weight;
                $DeletePawnSum->Pawn_Weight = $data1->Pawn_Weight;
                $DeletePawnSum->Amount = $data1->Amount;
                $DeletePawnSum->Total_Amount = $data1->Total_Amount;
                $DeletePawnSum->Interest = $data1->Interest;
                $DeletePawnSum->isRedeemed = $data1->isRedeemed;
                $DeletePawnSum->isForfeit = $data1->isForfeit;

                $DeletePawnSum->Reason = $reason;
                $DeletePawnSum->Deleted_by = $userName;
                $DeletePawnSum->Deleted_date = Carbon::now()->format('Y-m-d');

                $DeletePawnSum->PawnOC = $data1->OC;
                $DeletePawnSum->PawnBC = $data1->BC;
                $DeletePawnSum->OC = auth()->user()->username;
                $DeletePawnSum->BC = auth()->user()->BC;
                $DeletePawnSum->save();
            }

            foreach ($records2 as $data2) {
                 $DeletePawnDetails = new TDeletePawnDetails;
                 $DeletePawnDetails->Receipt_Number = $data2->Receipt_Number;
                 $DeletePawnDetails->Receipt_Type = $data2->Receipt_Type;
                 $DeletePawnDetails->Pawn_Type = "OP";
                 $DeletePawnDetails->Date = $data2->Date;
                 $DeletePawnDetails->Category = $data2->Category;
                 $DeletePawnDetails->Articles = $data2->Articles;
                 $DeletePawnDetails->Condition = $data2->Condition;
                 $DeletePawnDetails->Karatage = $data2->Karatage;
                 $DeletePawnDetails->Weight = $data2->Weight;
                 $DeletePawnDetails->Total_Weight = $data2->Total_Weight;
                 $DeletePawnDetails->QTY = $data2->QTY;
                 $DeletePawnDetails->Value = $data2->Value;
                 $DeletePawnDetails->IsRedeemed = $data2->IsRedeemed;

                 $DeletePawnDetails->PawnOC = $data2->OC;
                 $DeletePawnDetails->PawnBC = $data2->BC;
                 $DeletePawnDetails->OC = auth()->user()->username;
                 $DeletePawnDetails->BC = auth()->user()->BC;
                 $DeletePawnDetails->save();
            }

            TOpeningPawnSum::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC', $branch_code)
                ->delete();

            TOpeningPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC', $branch_code)
                ->delete();

            return response()->json([
                'status'=>'success',
            ]);

        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }

    }

    public function edit(Request $request)
    {
        $receiptNo = $request->receipt_no;
        $receiptType = $request->receipt_type;
        $branch_code = auth()->user()->BC;

        $records1 = TOpeningPawnSum::where('Receipt_Number', $receiptNo)
            ->where('BC', $branch_code)
            ->update(['Receipt_Type' => $receiptType]);

        $records2 = TOpeningPawnDetails::where('Receipt_Number', $receiptNo)
            ->where('BC', $branch_code)
            ->update(['Receipt_Type' => $receiptType]);

        // Check if the update was successful
        if ($records1 !== false && $records2 !== false) {
            return response()->json([
                'status'=>'success',
            ]);
        } else {
            return response()->json([
                'status'=>'not_found'
            ]);
        }

    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
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