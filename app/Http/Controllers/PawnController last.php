<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade as PDF;
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
use App\Models\Company;
use App\Models\branchDel;
use App\Models\TDeletePawnSum;
use App\Models\TDeletePawnDetails;


class PawnController extends Controller
{

    public function showPawnReceipt(){

        $branch_code =auth()->user()->BC;

        $itemCondition = itemCondition::all();
        $itemSetup = itemSetup::all();
        $karatageData = karatage::all();
        $receiptType = Recei_Add::all();
        $itemCategory = Category::all();
        $branchData = branchDel::latest()->paginate(1);

        // $maxReceiptNo = TPawnSum::orderBy('Receipt_Number', 'desc')->value('Receipt_Number');

        $maxReceiptNo = TPawnSum::where('BC',$branch_code)
                        ->orderBy('Receipt_Number','desc')
                        ->value('Receipt_Number');

        $maxReceiptNos = str_pad($maxReceiptNo, 4, '0', STR_PAD_LEFT);

        // get customer code max
        $maxCustomerCode = Customer::orderBy('Code', 'desc')
                            ->where('BC', $branch_code)
                            ->value('Code');

        $maxCustomerCodes = str_pad($maxCustomerCode, 4, '0', STR_PAD_LEFT);

        return view('pawnReceipt')
        ->with("itemCondition" , $itemCondition)
        ->with("itemSetup" , $itemSetup)
        ->with("receiptType" , $receiptType)
        ->with("itemCategory" , $itemCategory)
        ->with("itemKaratage", $karatageData)
        ->with("maxCustomer", $maxCustomerCodes)
         ->with("branchDetails", $branchData)
        ->with("maxReceipt", $maxReceiptNos);
       }

    public function showRedeemReceipt(){
        return view('RedeemReceipt');
    }


    public function storePawnSum(Request $request){
        $request->validate([
            'customer_nic' => ' required',
            'customer_name' => ' required',
            'customer_address' => ' required',
            'customer_contact_1' => ' required',
            'receipt_no' => ' required',
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
            'receipt_no' => ' The Receipt No field is required',
            'receipt_date' => ' The Date field is required',
            'amount' => ' The Amount field is required',
            'total_amount' => 'The Total Amount field is required',
            // 'oc' => ' The Operator field is required',
            // 'bc' => ' The Branch field is required'
        ]);


        $Receipt_Type = $request->receipt_type;
        $Receipt_Date = $request->receipt_date; 
        // Retrieve the first record that matches the receipt type
        $receipt = Recei_Add::where('receiptname', $Receipt_Type)->first();
        
        if ($receipt) {
            // Calculate the to_date by adding the validPeriod to the Receipt_Date
            $to_date = Carbon::parse($Receipt_Date)->addDays($receipt->validPeriod)->toDateString();
        } else {
            // Handle the case where the receipt type is not found
            $to_date = null; // or any default value or error handling
        }
        $PawnSum = new TPawnSum;
        $PawnSum->Customer_NIC = $request->customer_nic;
        $PawnSum->Customer_Name = $request->customer_name;
        $PawnSum->First_name = $request->first_name;
        $PawnSum->Middle_name = $request->middle_name;
        $PawnSum->Last_name = $request->last_name;
        $PawnSum->Customer_Address = $request->customer_address;
        $PawnSum->Customer_Phone = $request->customer_contact_1;
        $PawnSum->Receipt_Type = $request->receipt_type;
        $PawnSum->Receipt_Number = $request->receipt_no;
        $PawnSum->Invoice_Number = $request->receipt_no;
        $PawnSum->Ticket_Number = $request->ticket_no;
        $PawnSum->To_Date = $to_date;
        $PawnSum->Receipt_Date = $request->receipt_date;
        $PawnSum->RePawning_date = $request->receipt_date;
        $PawnSum->Total_Weight = $request->sum_total_weight;
        $PawnSum->Pawn_Weight = $request->sum_pawn_weight;
        $PawnSum->Amount = $request->amount;
        $PawnSum->Interest_Rate = $request->InterestRate;
        $PawnSum->Pawn_Amount = $request->amount;
        $PawnSum->Total_Amount = $request->total_amount;
        $PawnSum->Interest = $request->interest;
        $PawnSum->IsRedeemed = 0;
        $PawnSum->isForfeit = 0;
        $PawnSum->OC = auth()->user()->username;
        $PawnSum->BC = auth()->user()->BC;
        $PawnSum->save();

        foreach ($request -> inputs as $key=>$value){
        $PawnDetails = new TPawnDetails;
        $PawnDetails->Receipt_Number=$value['receipt_no'];
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
        $PawnDetails->OC=auth()->user()->username;
        $PawnDetails->BC=auth()->user()->BC;
        $PawnDetails->save();
        }

        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;

        $receiptInput_no = $request->receipt_no;
        $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                        ->where('BC', $branch_code)
                        ->get();

        $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                    ->where('BC', $branch_code)
                    ->get();

        $companyData = Company::latest()->paginate(1);

        $branch_name = auth()->user()->Branch;
        $branchData = branchDel::where('name', $branch_name)->get();

        // Generate the PDF content using a view
        $pdf = PDF::loadView('pawnReceiptPrint', [
            'pawnSumData' => $T_sumdata ,
            'pawnDetailsData' => $T_detailsdata,
            'companyData' => $companyData,
            'branchDetails'=> $branchData
            ]);

        $pdf2 = PDF::loadView('pawnReceiptTicketPrint', [
            'pawnSumData' => $T_sumdata ,
            'pawnDetailsData' => $T_detailsdata,
            'companyData' => $companyData,
            'branchDetails'=> $branchData
            ]);


        // Save the PDF to a temporary file
        $pdfPath = storage_path('../public/assets/pdf/Pawn_receipt'.$branch_code.'.pdf');
        $pdf->save($pdfPath);

        // Save the PDF to a temporary file
        $pdfPath2 = storage_path('../public/assets/pdf/Pawn_ticket_receipt'.$branch_code.'.pdf');
        $pdf2->save($pdfPath2);

        $pdfUrl1 = asset('public/assets/pdf/Pawn_receipt'.$branch_code.'.pdf');
        $pdfUrl2 = asset('public/assets/pdf/Pawn_ticket_receipt'.$branch_code.'.pdf');

        // Return the PDF as a download
        // return $pdf->stream();

        usleep(1000000);
        return back()
        ->with('done','The receipt has been added')
        ->with("pdfLink1", $pdfUrl1)
        ->with("pdfLink2", $pdfUrl2);

        // return $pdf->download('report.pdf');

    }

    public function printReceipt(Request $request){
        $receiptInput_no = $request->receipt_no;
        $branch_code = auth()->user()->BC;

        $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                            ->where('BC',$branch_code)
                            ->get();

        $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                        ->where('BC',$branch_code)
                        ->get();

        $companyData = Company::latest()->paginate(1);

        // Generate the PDF content using a view
        $pdf = PDF::loadView('pawnReceiptPrint', ['pawnSumData' => $T_sumdata , 'pawnDetailsData' => $T_detailsdata, 'companyData' => $companyData]);

        // Save the PDF to a temporary file
        // $pdfPath = storage_path('app/temp/pawn_receipt.pdf');
        $pdfPath = storage_path('../public/assets/pdf/Pawn_receipt.pdf');
        $pdf->save($pdfPath);

        // $pdfUrl = Storage::url('app/temp/pawn_receipt.pdf');
        $pdfUrl = asset('../public/assets/pdf/Pawn_receipt.pdf');
        // return $pdf->stream();

        // return $pdfPath;

        return response()->json([
            'status' => 'success',
            'pdf_url' => $pdfUrl
        ]);
    }

    //delete pawn receipt
    public function  deleteReceipt(Request $request){
        $receiptNo = $request->receipt_no;
        $receiptType = $request->receipt_type;
        $userName = $request->user_name;
        $reason = $request->reason;
        $op = $request->op;
        $branch_code = auth()->user()->BC;

        $records1 = TPawnSum::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC',$branch_code)
                ->get();

        $records2 = TPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC',$branch_code)
                ->get();

        if($records1 && $records2){

            foreach ($records1 as $data1) {
                $DeletePawnSum = new TDeletePawnSum;
                $DeletePawnSum->Customer_NIC = $data1->Customer_NIC;
                $DeletePawnSum->Customer_Name = $data1->Customer_Name;
                $DeletePawnSum->Customer_Address = $data1->Customer_Address;
                $DeletePawnSum->Customer_Phone = $data1->Customer_Phone;
                $DeletePawnSum->Receipt_Type = $data1->Receipt_Type;
                $DeletePawnSum->Pawn_Type = "P";
                $DeletePawnSum->Valid_Period = $data1->Valid_Period;
                $DeletePawnSum->Receipt_Number = $data1->Receipt_Number;
                $DeletePawnSum->Invoice_Number = $data1->Invoice_Number;
                $DeletePawnSum->Receipt_Date = $data1->Receipt_Date;
                $DeletePawnSum->Total_Weight = $data1->Total_Weight;
                $DeletePawnSum->Pawn_Weight = $data1->Pawn_Weight;
                $DeletePawnSum->Amount = $data1->Amount;
                $DeletePawnSum->Total_Amount = $data1->Total_Amount;
                $DeletePawnSum->Interest = $data1->InterestRate;
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
                 $DeletePawnDetails->Pawn_Type = "P";
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

            TPawnSum::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC',$branch_code)
                ->delete();

            TPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('Receipt_Type',$receiptType)
                ->where('BC',$branch_code)
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


    // show items according to category
    public function selectCategory(Request $request){
        $category = $request->category;
        $data = itemSetup::where('Category',$category)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }


    // get customer Using NIC
    public function get(Request $request){
        $nic =$request->search_string;
        $branch_code = auth()->user()->BC;

        $data = Customer::where('NIC', $nic)
                ->get();

        // $data = Customer::where('NIC', 'like', $request->search_string.'%');
        if($data->count()!=null){
            // dd($data );
            return view('pawning_search_customer')->with("customer_get", $data)->render();
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    // get customer using receipt number
    public function getCustomerReceiptNo(Request $request){
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $data = TPawnSum::where('Receipt_Number',$receiptNo)
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

        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;

        $receiptNo = $request->search_receipt_no;
        $data = TPawnDetails::where('Receipt_Number',$receiptNo)
                ->where('BC', $branch_code)
                ->get();

        $itemCondition = itemCondition::all();
        $itemSetup = itemSetup::all();
        $karatageData = karatage::all();
        $receiptType = Recei_Add::all();
        $itemCategory = Category::all();
        // dd($data);
        if($data->count() != null){
            // $cus_nic = $data->first()->Customer_NIC;
            // $cus_data = Customer::where('NIC', $cus_nic)->get();

            // $receipt_typ = $data->first()->Receipt_Type;
            // $receipt_data = Recei_Add::where('receiptname', $receipt_typ)->get();


            return view('pawning_get_receipt')
            // ->with('customerData', $cus_data)
            // ->with('receiptTypeData', $receipt_data)
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

        $data = TPawnDetails::where('Receipt_Number',$receiptNo)
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

    public function customerHistoryDetails(Request $request){
        $customerNic = $request->search_string;
        $branch_code = auth()->user()->BC;

        $data = TPawnSum::where('Customer_NIC',$customerNic)
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

    public function ValueCheck(Request $request)
    {
        $receiptValue = $request->search_receipt_no;
        $receipt_type = $request->receipt_type;
        $pawning_amount = $request->pawning_amount;
        $amount = $request->amount; // Now this will be correctly received
    
        // Query the rates based on the pawn amount
        $rate1 = Recei_Add::where('pawn_amount', '>=', 100000)->value('rate3');
        $rate2 = Recei_Add::whereBetween('pawn_amount', [50000, 99999])->value('rate3');
        $rate3 = Recei_Add::where('pawn_amount', '<', 50000)->value('rate3');
    
        // Determine the interest rate based on the receipt amount
        if ($receiptValue >= 100000) {
            $interestRate = $rate1;
        } elseif ($receiptValue >= 50000 && $receiptValue <= 99999) {
            $interestRate = $rate2;
        } else {
            $interestRate = $rate3;
        }
    
        // If receipt value exists, return view
        if ($receiptValue != null) {
            return view('pawning_get_total')
                ->with('amount', $amount)
                ->with('InterestRate', $interestRate)
                ->with('receiptData', $receiptValue);
        } else {
            return response()->json(['status' => 'not_found']);
        }
    }
    
    public function InterestSave(Request $request)
    {
        // Get the value from the AJAX request
        $amount = $request->input('amount');
    
        // Check if the amount is provided and is numeric
        if (!$amount || !is_numeric($amount)) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }
    
        // Query the rates based on the pawn amount
        $rate1 = Recei_Add::where('pawn_amount', '>=', 100000)->value('rate3');
        $rate2 = Recei_Add::whereBetween('pawn_amount', [50000, 99999])->value('rate3');
        $rate3 = Recei_Add::where('pawn_amount', '<', 50000)->value('rate3');
    
        // Determine the interest rate based on the receipt amount
        if ($amount >= 100000) {
            $interestRate = $rate1;
        } elseif ($amount >= 50000 && $amount <= 99999) {
            $interestRate = $rate2;
        } else {
            $interestRate = $rate3;
        }
    
        // Check if the interest rate was found, if not, return a default value
        if (!$interestRate) {
            return response()->json(['error' => 'No interest rate found for this amount'], 404);
        }

        // 1st months interest calucation
        $ValiledPeriodTotal = $amount/100 * $interestRate;
        $ValiledPeriod = $ValiledPeriodTotal;

        // Return a response with the amount and calculated interest rate
        return response()->json([
            'amount' => $amount,
            'interestRate' => $interestRate,
            'ValiledPeriod' => $ValiledPeriod
        ]);
    }
    
    
    
    
    
}