<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\TInvoiceSum;
use App\Models\TInvoiceDeils;
use App\Models\TItemMovement;
use App\Models\branchDel;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

public function index(Request $request)
{
    $branch_code = auth()->user()->BC;

    // Get max invoice numeric part for the branch, safely extract number after 'INV-'
    $maxInvoiceNo = TInvoiceSum::where('BC', $branch_code)
        ->selectRaw("MAX(CAST(SUBSTRING(Invoice_no, 5) AS UNSIGNED)) as max_no")
        ->value('max_no');

    $newNumber = $maxInvoiceNo ? $maxInvoiceNo + 1 : 1;

    // Format new invoice number with leading zeros
    $generatedInvoiceNo = 'INV-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

    $companyData = Company::latest()->paginate(1);
    $itemCode = Item::all();
    $itemDetails = Item::all();
    $Customerdata = Customer::all();
    $stockDetails = Item::where('BC', $branch_code)
                 ->where('IntoItem', 0)
                 ->where('SaleIsItem', 0)
                 ->get();

    $sumPurchase = $stockDetails->sum('purchasePrice');
    $quain = $stockDetails->sum('total_qun_in');
    $quaout = $stockDetails->sum('total_qun_out');

    return view('salesInvoice')
        ->with("stockDetails", $stockDetails)
        ->with("quain", $quain)
        ->with("quaout", $quaout)
        ->with("itemCode", $itemCode)
        ->with("companyData", $companyData)
        ->with("customerDetails", $Customerdata)
        ->with("itemDetails", $itemDetails)
        ->with("generatedInvoiceNo", $generatedInvoiceNo); // Pass generated invoice no
}




    public function get(Request $request)
    {
        $jobNo =$request->search_string;
        $data = JobSheet::where('Job_no', $jobNo)->get();
        if($data->count() != 0){
            // dd($data );
            return response()->json([
                'status'=>'success',
                'jobNo'=> $data,
            ]);
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

public function createInvoice(Request $request)
{
    // Validate input fields except invoice_no (auto-generated)
    $request->validate([
        'inputs.*.invoice_date' => 'required|date',
        'inputs.*.item_code' => 'required|string',
        'inputs.*.qty' => 'required|numeric|min:1',
        'inputs.*.unit_price' => 'required|numeric|min:0',
        'inputs.*.net_value' => 'required|numeric|min:0',
    ], [
        'inputs.*.invoice_date.required' => 'The Invoice Date field is required',
        'inputs.*.item_code.required' => 'The Item Code field is required',
        'inputs.*.qty.required' => 'The QTY field is required',
        'inputs.*.unit_price.required' => 'The Unit Price field is required',
        'inputs.*.net_value.required' => 'The Net Value field is required',
    ]);

    DB::beginTransaction();

    try {
        // Generate new Invoice_no (incremental)
        $lastInvoice = TInvoiceSum::orderBy('id', 'desc')->first();

        if ($lastInvoice) {
            $lastNumber = intval(str_replace('INV-', '', $lastInvoice->Invoice_no));
            $newInvoiceNo = 'INV-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newInvoiceNo = 'INV-000001';
        }

        // Save Invoice Summary
        $InvoiceSum = new TInvoiceSum;
        $InvoiceSum->Invoice_no = $newInvoiceNo;
        $InvoiceSum->Invoice_date = $request->invoice_date;
        $InvoiceSum->Customer_NIC = $request->customer_nic ?? null;
        $InvoiceSum->Customer_Name = $request->customer_name ?? null;
        $InvoiceSum->Customer_Phone = $request->customer_phone ?? null;
        $InvoiceSum->Customer_Address = $request->customer_address ?? null;
        $InvoiceSum->Gross_Amount = $request->gross_amount ?? 0;
        $InvoiceSum->Discount = $request->discount ?? 0;
        $InvoiceSum->Net_Amount = $request->net_amount ?? 0;
        $InvoiceSum->Cash_Pay = $request->cash_payment ?? 0;
        $InvoiceSum->Credite = $request->credite_payment ?? 0;
        $InvoiceSum->Cheque = $request->cheque_payment ?? 0;
        $InvoiceSum->serial_number = $request->serial_numbers_text ?? null;
        $InvoiceSum->BC = auth()->user()->BC;
        $InvoiceSum->OC = auth()->user()->username;
        $InvoiceSum->save();

        // Save Invoice Details and Item Movements
        foreach ($request->inputs as $value) {
            $InvoiceDetails = new TInvoiceDeils;
            $InvoiceDetails->Invoice_no = $newInvoiceNo;
            $InvoiceDetails->Invoice_date = $value['invoice_date'];
            $InvoiceDetails->Item_code = $value['item_code'];
            $InvoiceDetails->Item_s_code = $value['item_s_code'] ?? null;
            $InvoiceDetails->Item_description = $value['item_description'] ?? null;
            $InvoiceDetails->QTY = $value['qty'];
            $InvoiceDetails->Unit_price = $value['unit_price'];
            $InvoiceDetails->Discount = $value['discount_val'] ?? 0;
            $InvoiceDetails->Net_value = $value['net_value'];
            $InvoiceDetails->OC = auth()->user()->username;
            $InvoiceDetails->BC = auth()->user()->BC;
            $InvoiceDetails->save();

            $ItemMovementDetails = new TItemMovement;
            $ItemMovementDetails->trans_no = $newInvoiceNo;
            $ItemMovementDetails->dDate = $value['invoice_date'];
            $ItemMovementDetails->trans_code = "SALES";
            $ItemMovementDetails->item_code = $value['item_code'];
            $ItemMovementDetails->qun_out = $value['qty'];
            $ItemMovementDetails->bc = auth()->user()->BC;
            $ItemMovementDetails->save();
            
            
             $branch_code = auth()->user()->BC;

            Item::where('Item_code', $value['item_code'])
                ->where('BC', $branch_code)
                ->update(['SaleIsItem' => 1]);
        }

        DB::commit();

        // Prepare data for PDF
        $branch_code = auth()->user()->BC;
        $companyData = Company::latest()->paginate(1);
        $T_detailsdata = Item::where('Item_code', $value['item_code'])->where('BC', $branch_code)->get();
        $T_sumdata = TInvoiceSum::where('Invoice_no', $newInvoiceNo)->where('BC', $branch_code)->get();
        $M_Branch = branchDel::where('bccode', $branch_code)->get();
        $T_customerdata = Customer::where('Code', $request->customer_nic)->get();

        // Generate single PDF view
        $pdf = PDF::loadView('repairInvoicePrint', [
            'advance' => null,
            'pawnSumData' => $T_sumdata,
            'customerData' => $T_customerdata,
            'pawnDetailsData' => $T_detailsdata,
            'companyData' => $companyData,
            'branchData' => $M_Branch,
        ]);

        $pdfPath = storage_path('../public/assets/pdf/repairInvoicePrint'.$branch_code.'.pdf');
        $pdf->save($pdfPath);

        $pdfUrl = asset('public/assets/pdf/repairInvoicePrint'.$branch_code.'.pdf');


        return back()
            ->with('done', 'The Invoice has been added')
            ->with("pdfLink", $pdfUrl);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Failed to create Invoice: ' . $e->getMessage()]);
    }
}

    // print invoice


    // get and show items according to category


     // get and show items according to category
     public function setItemDescription(Request $request){
        $Item_code = $request->Item_code;
        $data = Item::where('Item_code',$Item_code)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // find invoice details
    public function findInvoice(Request $request){
        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;
        $receiptNo = $request->search_receipt_no;
        $data = TInvoiceDeils::where('Invoice_no',$receiptNo)
                    ->where('BC',$branch_code)
                    ->get();
        if($data->count() != null){
            return view('invoice_find_tInvoiceDetails')
            // ->with("itemsDetails" , $itemsData)
            ->with('invoiceData', $data);
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }

    // find invoice customer data
    public function findInvoiceCustomerData(Request $request){
        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;
        $receiptNo = $request->search_receipt_no;
        $data = TInvoiceSum::where('Invoice_no',$receiptNo)
                ->where('BC',$branch_code)
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


    public function PrintPOS(Request $request){
        $invoiceInput_no = $request->invoice_no;
        $companyData = Company::latest()->paginate(1);
        $branch_code = auth()->user()->BC;
        $user_name = auth()->user()->username;

        $T_detailsdata = TInvoiceDeils::where('Invoice_no', $invoiceInput_no)
                        ->where('BC',$branch_code)
                        ->get();

       $T_sumdata = TInvoiceSum::where('Invoice_no', $invoiceInput_no)
                    ->where('BC', $branch_code)
                    ->get();

        $data_bool = TInvoiceSum::where('Invoice_no', $invoiceInput_no)
                    ->where('BC', $branch_code)
                    ->first();

        if ($T_sumdata) {
            $customer_nic = $data_bool->Customer_NIC;
        }else{
            return response()->json(['status' => 'not_found']);
        }

        $T_customerdata = Customer::where('Code', $customer_nic)
                        ->get();

        if($T_sumdata->count() != null){
            // Generate the PDF content using a view
           if (Auth::check() && Auth::user()->BC == '001') {
            $pdf = PDF::loadView('repairInvoicePrint', [
                'advance' => $advance,
                'pawnSumData' => $T_sumdata,
                'customerData' => $T_customerdata,
                'pawnDetailsData' => $T_detailsdata,
                'companyData' => $companyData
            ]);
        } elseif (Auth::check() && Auth::user()->BC == '002') {
            $pdf = PDF::loadView('repairInvoicepos', [
                'advance' => $advance,
                'pawnSumData' => $T_sumdata,
                'customerData' => $T_customerdata,
                'pawnDetailsData' => $T_detailsdata,
                'companyData' => $companyData
            ]);
        }

            // Save the PDF to a temporary file
            $pdfPath = storage_path('../public/assets/pdf/Sales_Invoice.pdf');
            $pdf->save($pdfPath);
            $pdfUrl = asset('/public/assets/pdf/Sales_Invoice.pdf');

            return response()->json(['status' => 'success', 'pdfUrl' => $pdfUrl]);
        }else{
            return response()->json(['status' => 'not_found']);
        }
    }


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


    public function destroy(Request $request)
    {
        Invoice::find($request->invoice_id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }
}