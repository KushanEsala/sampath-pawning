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
use App\Models\Company;
use App\Models\branchDel;
use App\Models\TDeletePawnSum;
use App\Models\TOpeningPawnSum;
use App\Models\TDeletePawnDetails;
use App\Models\TPawnTrans;
use Illuminate\Support\Facades\DB;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptTypeResolver;


class PawnController extends Controller
{

    public function showPawnReceipt(?ReceiptTypeResolver $resolver = null){
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);

        $branch_code = auth()->user()->BC;

        $itemCondition  = itemCondition::all();
        $itemSetup      = itemSetup::all();
        $karatageData   = karatage::all();
        $receiptType    = $resolver->getActiveTypes();
        $itemCategory   = Category::all();
        $branchData     = branchDel::latest()->paginate(1);

        $maxReceiptNo = TPawnSum::where('BC', $branch_code)
                        ->orderBy('Receipt_Number', 'desc')
                        ->value('Receipt_Number');

        $maxReceiptNos = str_pad($maxReceiptNo, 4, '0', STR_PAD_LEFT);

        $Ticket_Number = TPawnSum::where('BC', $branch_code)
                        ->orderBy('Receipt_Number', 'desc')
                        ->value('Receipt_Number');

        $maxTicket_NumberNos = str_pad($Ticket_Number, 4, '0', STR_PAD_LEFT);

        // get customer code max
        $maxCustomerCode = Customer::orderBy('Code', 'desc')
                            ->where('BC', $branch_code)
                            ->value('Code');

        $maxCustomerCodes = str_pad($maxCustomerCode, 4, '0', STR_PAD_LEFT);

        $Receipt_Number = TPawnSum::where('BC', $branch_code)
                            ->orderBy('Ticket_number', 'desc')
                            ->value('Ticket_number');

        $nextReceiptNo = ($Receipt_Number ?? 0) + 1;
        $maxReceiptNosReceipt_Number = str_pad($nextReceiptNo, '0', STR_PAD_LEFT);

        $currentTime = date('Hi');
        $receiptNo   = str_pad($maxReceiptNosReceipt_Number, 4, '0', STR_PAD_LEFT);
        $invoiceNo   = $maxReceiptNosReceipt_Number;

        return view('pawnReceipt')
            ->with('itemCondition', $itemCondition)
            ->with('itemSetup', $itemSetup)
            ->with('receiptType', $receiptType)
            ->with('itemCategory', $itemCategory)
            ->with('itemKaratage', $karatageData)
            ->with('maxCustomer', $maxCustomerCodes)
            ->with('branchDetails', $branchData)
            ->with('maxTicket', $maxTicket_NumberNos)
            ->with('maxReceipt', $maxReceiptNos)
            ->with('invoiceNo', $invoiceNo);
    }

    public function showRedeemReceipt(){
        return view('RedeemReceipt');
    }

    public function searchCustomerAutocomplete(Request $request){
        $query       = $request->get('query');
        $branch_code = auth()->user()->BC;

        if(strlen($query) < 2){
            return response()->json([]);
        }

        $customers = Customer::where('BC', $branch_code)
            ->where(function($q) use ($query){
                $q->where('NIC', 'LIKE', "%{$query}%")
                  ->orWhere('First_name', 'LIKE', "%{$query}%")
                  ->orWhere('Last_name', 'LIKE', "%{$query}%")
                  ->orWhere('Contact_1', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($customers);
    }


    public function storePawnSum(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $request->validate([
            'customer_nic'        => 'required',
            'customer_name'       => 'required',
            'customer_address'    => 'required',
            'customer_contact_1'  => 'required',
            'receipt_date'        => 'required',
            'amount'              => 'required',
            'total_amount'        => 'required',
            'inputs.*.category'   => 'required',
            'inputs.*.articles'   => 'required',
            'inputs.*.condition'  => 'required',
            'inputs.*.karatage'   => 'required',
            'inputs.*.weight'     => 'required',
            'inputs.*.qty'        => 'required',
            'inputs.*.value'      => 'required'
        ], [
            'customer_nic.required'       => 'The NIC field is required',
            'customer_name.required'      => 'The Name field is required',
            'customer_address.required'   => 'The Address field is required',
            'customer_contact_1.required' => 'The Contact field is required',
            'receipt_date.required'       => 'The Date field is required',
            'amount.required'             => 'The Amount field is required',
            'total_amount.required'       => 'The Total Amount field is required',
        ]);

        DB::beginTransaction();

        try {
            $branch_code  = auth()->user()->BC;
            $Receipt_Type = $request->receipt_type;
            $Receipt_Date = $request->receipt_date;

            // ============================================
            // Check if any category is SILVER
            // ============================================
            $hasSilverCategory = false;
            foreach ($request->inputs as $input) {
                if (isset($input['category']) && strtoupper($input['category']) == 'SILVER') {
                    $hasSilverCategory = true;
                    break;
                }
            }

            // If SILVER category found, override the receipt type
            if ($hasSilverCategory) {
                $Receipt_Type = 'SILVER';
            }

            // ============================================
            // Get next numbers with row locking
            // ============================================
            $maxReceiptNo  = TPawnSum::where('BC', $branch_code)->lockForUpdate()->max('Receipt_Number');
            $nextReceiptNo = ($maxReceiptNo ?? 0) + 1;

            $maxTicketNo  = TPawnSum::where('BC', $branch_code)->lockForUpdate()->max('Ticket_Number');
            $nextTicketNo = ($maxTicketNo ?? 0) + 1;

            $maxInvoiceNo  = TPawnSum::where('BC', $branch_code)->lockForUpdate()->max('Invoice_Number');
            $nextInvoiceNo = ($maxInvoiceNo ?? 0) + 1;

            // ============================================
            // STEP 1: Fetch receipt type data FIRST
            // ============================================
            $receipt = $resolver->resolveByDate($Receipt_Type, $Receipt_Date);

            $isSilverReceipt = strtoupper((string) $Receipt_Type) === 'SILVER';
            if ($isSilverReceipt && !$receipt) {
                throw new \RuntimeException('The SILVER receipt type must be configured before issuing a Silver pawn.');
            }
            if ($receipt) {
                $to_date = Carbon::parse($Receipt_Date)->addDays(
                    $isSilverReceipt ? \App\Services\SilverInterest::validDays($receipt) : (int) $receipt->validPeriod
                )->toDateString();
            } else {
                $to_date = null;
            }

            $receiptDate = Carbon::parse($request->receipt_date);
            $validPeriod = (int) $request->Valid_Period;
            $finalDate = $receiptDate->copy()->addMonths(max(1, $validPeriod));

            // ============================================
            // STEP 2: Build TPawnSum
            // ============================================
            $PawnSum                   = new TPawnSum;
            $PawnSum->Customer_NIC     = $request->customer_nic;
            $PawnSum->Customer_Name    = $request->customer_name;
            $PawnSum->First_name       = $request->first_name;
            $PawnSum->Middle_name      = $request->middle_name;
            $PawnSum->Last_name        = $request->last_name;
            $PawnSum->Customer_Address = $request->customer_address;
            $PawnSum->Customer_Phone   = $request->customer_contact_1;
            $PawnSum->Receipt_Type     = $Receipt_Type;
            $PawnSum->Receipt_Number   = $nextReceiptNo;
            $PawnSum->Invoice_Number   = $nextInvoiceNo;
            $PawnSum->Ticket_Number    = $nextTicketNo;
            $PawnSum->To_Date          = $to_date;
            $PawnSum->Receipt_Date     = $request->receipt_date;
            $PawnSum->RePawning_date   = $request->receipt_date;
            $PawnSum->Pawn_Date        = $request->receipt_date;
            $PawnSum->Total_Weight     = $request->sum_total_weight;
            $PawnSum->Pawn_Weight      = $request->sum_pawn_weight;
            $PawnSum->Amount           = $request->amount;
            $PawnSum->Interest_Rate    = $isSilverReceipt ? (float) $receipt->rate1 : $request->InterestRate;
            $PawnSum->Valid_Period     = $validPeriod;
            $PawnSum->Final_date       = $finalDate;
            $PawnSum->Pawn_Amount      = $request->amount;
            $PawnSum->Total_Amount     = $request->total_amount;
            $PawnSum->RePawning_amount = $request->amount;
            $PawnSum->Bill_System_bill_type  = 'NEW_SYSTEM';
            $PawnSum->IsRedeemed       = 0;
            $PawnSum->isForfeit        = 0;
            $PawnSum->OC               = auth()->user()->username;
            $PawnSum->BC               = $branch_code;

            // ============================================
            // STEP 3: Snapshot receipt config into pawn sum
            // ============================================
            if ($receipt) {
                $PawnSum->receiptname      = $receipt->receiptname;
                $PawnSum->documentCharges  = $receipt->documentCharges;
                $PawnSum->service_charge   = $receipt->service_charge;
                $PawnSum->stampduty        = $receipt->stampduty;
                $PawnSum->validPeriod      = $receipt->validPeriod;
                $PawnSum->period1          = $receipt->period1;
                $PawnSum->rate1            = $receipt->rate1;
                $PawnSum->period2          = $receipt->period2;
                $PawnSum->rate2            = $receipt->rate2;
                $PawnSum->period3          = $receipt->period3;
                $PawnSum->rate3            = $receipt->rate3;
                $PawnSum->pawn_amount_two   = $receipt->pawn_amount;
                $PawnSum->Postage_charge    = $receipt->Postage_charge;
                if (\Illuminate\Support\Facades\Schema::hasColumn('t_pawn_sums', 'letter_1_days')) {
                    foreach (\App\Services\ReceiptPenaltySchedule::FIELDS as $field) {
                        $PawnSum->{$field} = (int) ($receipt->{$field} ?? 21);
                    }
                }
                $PawnSum->s_charge_less    = $receipt->s_charge_less;
                $PawnSum->s_charge_greater = $receipt->s_charge_greater;
            }

            // ============================================
            // STEP 4: Save TPawnSum
            // ============================================
            $PawnSum->save();

            // ============================================
            // Save TPawnTrans
            // ============================================
            $TPawnTrans                = new TPawnTrans;
            $TPawnTrans->Customer_NIC  = $request->customer_nic;
            $TPawnTrans->Customer_Name = $request->customer_name;
            $TPawnTrans->code          = $nextReceiptNo;
            $TPawnTrans->trans_no      = $nextReceiptNo;
            $TPawnTrans->trans_type    = "PAWN";
            $TPawnTrans->trans_amount  = $request->amount;
            $TPawnTrans->dDate         = $request->receipt_date;
            $TPawnTrans->Cr_amount     = $request->amount;
            $TPawnTrans->Pawn_Amount   = $request->amount;
            $TPawnTrans->Dr_amount     = 0;
            $TPawnTrans->Extend_Date   = $finalDate;
            $TPawnTrans->OC            = auth()->user()->username;
            $TPawnTrans->BC            = $branch_code;
            $TPawnTrans->save();

            $karatages = [];

            // ============================================
            // Save TPawnDetails
            // ============================================
            foreach ($request->inputs as $key => $value) {
                $PawnDetails               = new TPawnDetails;
                $PawnDetails->Receipt_Number = $nextReceiptNo;
                $PawnDetails->Receipt_Type   = $value['receipt_type'];
                $PawnDetails->Date           = $value['receipt_date'];
                $PawnDetails->Category       = $value['category'];
                $PawnDetails->Articles       = $value['articles'];
                $PawnDetails->Condition      = $value['condition'];
                $PawnDetails->Karatage       = $value['karatage'];
                $PawnDetails->Weight         = $value['weight'];
                $PawnDetails->Total_Weight   = $value['total_weight'];
                $PawnDetails->QTY            = $value['qty'];
                $PawnDetails->Value          = $value['value'];
                $PawnDetails->value_market   = $value['value_market'];
                $PawnDetails->OC             = auth()->user()->username;
                $PawnDetails->BC             = $branch_code;
                $PawnDetails->save();

                if (!in_array($value['karatage'], $karatages)) {
                    $karatages[] = $value['karatage'];
                }
            }

            // ============================================
            // Accounting Entry for Pawn Loan
            // ============================================
            \App\Services\ReceiptArticleStatus::sync($PawnSum);
            $pawnLoansAccountId = 1; // Replace with actual Pawn Loans account ID
            $cashAccountId      = 2; // Replace with actual Cash account ID

            DB::table('t_account_trans')->insert([
                'trans_date'   => $request->receipt_date,
                'voucher_no'   => 'PWN-' . $nextInvoiceNo,
                'account_id'   => $cashAccountId,
                'related_id'   => $nextInvoiceNo,
                'related_type' => 'PAWN',
                'Invoice_no'   => $nextInvoiceNo,
                'dr'           => 0,
                'cr'           => $request->amount,
                'description'  => 'Cash disbursed for pawn loan - Receipt #' . $nextInvoiceNo,
                'branch_code'  => $branch_code,
                'created_by'   => auth()->user()->username,
                'created_at'   => now(),
                'updated_at'   => now()
            ]);

            // ============================================
            // Get data for PDF generation
            // ============================================
            $T_detailsdata = TPawnDetails::where('Receipt_Number', $nextReceiptNo)
                ->where('BC', $branch_code)
                ->get();

            $T_sumdata = TPawnSum::where('Receipt_Number', $nextReceiptNo)
                ->where('BC', $branch_code)
                ->get();

            $karatage_data   = karatage::whereIn('descrption', $karatages)->get();
            $companyData     = Company::latest()->paginate(1);
            $branchData      = branchDel::where('bccode', $branch_code)->first();
            $receiptTypeData = $receipt;

            // ============================================
            // Generate PDFs
            // ============================================
            $pdf = PDF::loadView('pawnReceiptPrint', [
                'pawnSumData'     => $T_sumdata,
                'pawnDetailsData' => $T_detailsdata,
                'companyData'     => $companyData,
                'branchData'      => $branchData,
                'karatage_data'   => $karatage_data,
                'T_Receipt_Type'  => $receiptTypeData
            ]);

            $pdf2 = PDF::loadView('pawnReceiptTicketPrint', [
                'pawnSumData'     => $T_sumdata,
                'pawnDetailsData' => $T_detailsdata,
                'companyData'     => $companyData,
                'branchData'      => $branchData,
                'karatage_data'   => $karatage_data,
                'T_Receipt_Type'  => $receiptTypeData
            ]);

            // Save PDFs
            $pdfPath = storage_path('../public/assets/pdf/Pawn_receipt_' . $branch_code . '_' . $nextReceiptNo . '.pdf');
            $pdf->save($pdfPath);

            $pdfPath2 = storage_path('../public/assets/pdf/Pawn_ticket_receipt_' . $branch_code . '_' . $nextReceiptNo . '.pdf');
            $pdf2->save($pdfPath2);

            $pdfUrl1 = asset('public/assets/pdf/Pawn_receipt_' . $branch_code . '_' . $nextReceiptNo . '.pdf');
            $pdfUrl2 = asset('public/assets/pdf/Pawn_ticket_receipt_' . $branch_code . '_' . $nextReceiptNo . '.pdf');

            DB::commit();

            usleep(1000000);

            return back()
                ->with('done', 'The receipt has been added. Receipt No: ' . $nextReceiptNo)
                ->with('pdfLink1', $pdfUrl1)
                ->with('pdfLink2', $pdfUrl2)
                ->with('generatedReceiptNo', $nextReceiptNo);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                return back()
                    ->with('error', 'Receipt number conflict detected. Please try again.')
                    ->withInput();
            }

            return back()
                ->with('error', 'Database error: ' . $e->getMessage())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'An error occurred: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function printReceipt(Request $request, ?ReceiptTypeResolver $resolver = null){
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $receiptInput_no = $request->receipt_no;
        $branch_code     = auth()->user()->BC;

        $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
                            ->where('BC', $branch_code)
                            ->get();

        $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
                        ->where('BC', $branch_code)
                        ->get();

        $companyData = Company::latest()->paginate(1);

        $karatages = [];
        foreach ($T_detailsdata as $detail) {
            if (!in_array($detail->Karatage, $karatages)) {
                $karatages[] = $detail->Karatage;
            }
        }

        $branchData      = branchDel::where('bccode', $branch_code)->first();
        $karatage_data   = karatage::whereIn('descrption', $karatages)->get();
        $Receipt_Type    = $T_sumdata->first()->Receipt_Type ?? null;
        $receiptTypeData = $T_sumdata->first() ? $resolver->resolveForReceipt($T_sumdata->first()) : null;

        $pdf = PDF::loadView('pawnReceiptTicketPrint', [
            'pawnSumData'     => $T_sumdata,
            'pawnDetailsData' => $T_detailsdata,
            'companyData'     => $companyData,
            'karatage_data'   => $karatage_data,
            'branchData'      => $branchData,
            'T_Receipt_Type'  => $receiptTypeData
        ]);

        $pdf2 = PDF::loadView('pawnReceiptTicketPrint', [
            'pawnSumData'     => $T_sumdata,
            'pawnDetailsData' => $T_detailsdata,
            'companyData'     => $companyData,
            'branchData'      => $branchData,
            'karatage_data'   => $karatage_data,
            'T_Receipt_Type'  => $receiptTypeData
        ]);

        $pdfPath = storage_path('../public/assets/pdf/Pawn_receipt' . $branch_code . '.pdf');
        $pdf->save($pdfPath);

        $pdfPath2 = storage_path('../public/assets/pdf/Pawn_ticket_receipt' . $branch_code . '.pdf');
        $pdf2->save($pdfPath2);

        $pdfUrl = asset('public/assets/pdf/Pawn_receipt' . $branch_code . '.pdf');

        return response()->json([
            'status'  => 'success',
            'pdf_url' => $pdfUrl
        ]);
    }

    public function deleteReceipt(Request $request){
        $receiptNo   = $request->receipt_no;
        $receiptType = $request->receipt_type;
        $userName    = $request->user_name;
        $reason      = $request->reason;
        $op          = $request->op;
        $branch_code = auth()->user()->BC;

        $records1 = TPawnSum::where('Receipt_Number', $receiptNo)
                ->where('Receipt_Type', $receiptType)
                ->where('BC', $branch_code)
                ->get();

        $records2 = TPawnDetails::where('Receipt_Number', $receiptNo)
                ->where('Receipt_Type', $receiptType)
                ->where('BC', $branch_code)
                ->get();

        if($records1 && $records2){

            foreach ($records1 as $data1) {
                $DeletePawnSum                  = new TDeletePawnSum;
                $DeletePawnSum->Customer_NIC    = $data1->Customer_NIC;
                $DeletePawnSum->Customer_Name   = $data1->Customer_Name;
                $DeletePawnSum->Customer_Address= $data1->Customer_Address;
                $DeletePawnSum->Customer_Phone  = $data1->Customer_Phone;
                $DeletePawnSum->Receipt_Type    = $data1->Receipt_Type;
                $DeletePawnSum->Pawn_Type       = "P";
                $DeletePawnSum->Valid_Period     = $data1->Valid_Period;
                $DeletePawnSum->Receipt_Number  = $data1->Receipt_Number;
                $DeletePawnSum->Invoice_Number  = $data1->Invoice_Number;
                $DeletePawnSum->Receipt_Date    = $data1->Receipt_Date;
                $DeletePawnSum->Total_Weight    = $data1->Total_Weight;
                $DeletePawnSum->Pawn_Weight     = $data1->Pawn_Weight;
                $DeletePawnSum->Amount          = $data1->Amount;
                $DeletePawnSum->Total_Amount    = $data1->Total_Amount;
                $DeletePawnSum->Interest        = $data1->InterestRate;
                $DeletePawnSum->isRedeemed      = $data1->isRedeemed;
                $DeletePawnSum->isForfeit       = $data1->isForfeit;
                $DeletePawnSum->Reason          = $reason;
                $DeletePawnSum->Deleted_by      = $userName;
                $DeletePawnSum->Deleted_date    = Carbon::now()->format('Y-m-d');
                $DeletePawnSum->PawnOC          = $data1->OC;
                $DeletePawnSum->PawnBC          = $data1->BC;
                $DeletePawnSum->OC              = auth()->user()->username;
                $DeletePawnSum->BC              = auth()->user()->BC;
                $DeletePawnSum->save();
            }

            foreach ($records2 as $data2) {
                $DeletePawnDetails               = new TDeletePawnDetails;
                $DeletePawnDetails->Receipt_Number = $data2->Receipt_Number;
                $DeletePawnDetails->Receipt_Type   = $data2->Receipt_Type;
                $DeletePawnDetails->Pawn_Type      = "P";
                $DeletePawnDetails->Date           = $data2->Date;
                $DeletePawnDetails->Category       = $data2->Category;
                $DeletePawnDetails->Articles       = $data2->Articles;
                $DeletePawnDetails->Condition      = $data2->Condition;
                $DeletePawnDetails->Karatage       = $data2->Karatage;
                $DeletePawnDetails->Weight         = $data2->Weight;
                $DeletePawnDetails->Total_Weight   = $data2->Total_Weight;
                $DeletePawnDetails->QTY            = $data2->QTY;
                $DeletePawnDetails->Value          = $data2->Value;
                $DeletePawnDetails->IsRedeemed     = $data2->IsRedeemed;
                $DeletePawnDetails->PawnOC         = $data2->OC;
                $DeletePawnDetails->PawnBC         = $data2->BC;
                $DeletePawnDetails->OC             = auth()->user()->username;
                $DeletePawnDetails->BC             = auth()->user()->BC;
                $DeletePawnDetails->save();
            }

            TPawnSum::where('Receipt_Number', $receiptNo)
                ->where('Receipt_Type', $receiptType)
                ->where('BC', $branch_code)
                ->delete();

            TPawnDetails::where('Receipt_Number', $receiptNo)
                ->where('Receipt_Type', $receiptType)
                ->where('BC', $branch_code)
                ->delete();

            return response()->json([
                'status' => 'success',
            ]);

        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    public function selectCategory(Request $request){
        $category = $request->category;
        $data     = itemSetup::where('Category', $category)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }

public function get(Request $request)
{
    $nic = $request->search_string;
    $branch_code = auth()->user()->BC;

    $customers = Customer::where('NIC', $nic)->get();

    $pendingPawn = TPawnSum::where('Customer_NIC', $nic)
        ->where('BC', $branch_code)
        ->where('IsRedeemed', 0)
        ->count();

    $pendingPawnTotal = TPawnSum::where('Customer_NIC', $nic)
        ->where('BC', $branch_code)
        ->where('IsRedeemed', 0)
        ->sum('Pawn_Amount');

    $redeemedPawn = TPawnSum::where('Customer_NIC', $nic)
        ->where('BC', $branch_code)
        ->where('IsRedeemed', 1)
        ->count();

    $Limit_Amount =  Customer::where('NIC', $nic)
        ->sum('Limit_Amount');


    $Limit_Pawn_Count =  Customer::where('NIC', $nic)
        ->sum('Limit_Pawn_Count');




    if ($customers->count() > 0) {
        return view('pawning_search_customer', [
            'customer_get'   => $customers,
            'pending_count'  => $pendingPawn,
            'redeemed_count' => $redeemedPawn,
            'pendingPawnTotal' => $pendingPawnTotal,
            'Limit_Amount' => $Limit_Amount,
            'Limit_Pawn_Count' => $Limit_Pawn_Count,

        ])->render();
    }

    return response()->json([
        'status' => 'not_found'
    ]);
}

    public function getCustomerReceiptNo(Request $request){
        $receiptNo   = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $data = TPawnSum::where('Receipt_Number', $receiptNo)
                ->where('BC', $branch_code)
                ->get();

        if($data->count() != null){
            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);

            return view('pawning_search_customer_using_receipt_no')->with("customer_get", $data)->render();
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    public function search(Request $request, ?ReceiptTypeResolver $resolver = null){
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $branch_code = auth()->user()->BC;
        $user_name   = auth()->user()->username;
        $receiptNo   = $request->search_receipt_no;

        $data = TPawnDetails::where('Receipt_Number', $receiptNo)
                ->where('BC', $branch_code)
                ->get();

        $itemCondition = itemCondition::all();
        $itemSetup     = itemSetup::all();
        $karatageData  = karatage::all();
        $receiptType   = $resolver->getActiveTypes();
        $itemCategory  = Category::all();

        if($data->count() != null){
            return view('pawning_get_receipt')
                ->with('itemCondition', $itemCondition)
                ->with('itemSetup', $itemSetup)
                ->with('itemKaratage', $karatageData)
                ->with('receiptType', $receiptType)
                ->with('itemCategory', $itemCategory)
                ->with('receiptData', $data);
        } else {
            return response()->json([
                'status' => 'not_found'
            ]);
        }
    }

    public function getArticleDetails(Request $request){
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

    public function customerHistoryDetails(Request $request){
        $customerNic = $request->search_string;
        $branch_code = auth()->user()->BC;

        $pawnSums = TPawnSum::where('Customer_NIC', $customerNic)
                    ->where('BC', $branch_code)
                    ->get();

        if($pawnSums->count() > 0){
            $data = [];

            foreach($pawnSums as $pawnSum){
                $pawnDetails = DB::table('t_pawn_details')
                                ->where('Receipt_Number', $pawnSum->Receipt_Number)
                                ->where('BC', $branch_code)
                                ->get();

                $data[] = [
                    'pawn_sum'     => $pawnSum,
                    'pawn_details' => $pawnDetails
                ];
            }

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

    public function ValueCheck(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver      = $resolver ?? app(ReceiptTypeResolver::class);
        $receiptValue  = $request->search_receipt_no;
        $receipt_type  = $request->receipt_type;
        $pawning_amount= $request->pawning_amount;
        $amount        = $request->amount;

        $activeRates = $resolver->getActiveTypes()->reject(fn ($type) => strtoupper((string) $type->receiptname) === 'SILVER');
        $rate1 = $activeRates->where('pawn_amount', '>=', 100000)->value('rate3');
        $rate2 = $activeRates->whereBetween('pawn_amount', [50000, 99999])->value('rate3');
        $rate3 = $activeRates->where('pawn_amount', '<', 50000)->value('rate3');

        if ($receiptValue >= 100000) {
            $interestRate = $rate1;
        } elseif ($receiptValue >= 50000 && $receiptValue <= 99999) {
            $interestRate = $rate2;
        } else {
            $interestRate = $rate3;
        }

        if ($receiptValue != null) {
            return view('pawning_get_total')
                ->with('amount', $amount)
                ->with('InterestRate', $interestRate)
                ->with('receiptData', $receiptValue);
        } else {
            return response()->json(['status' => 'not_found']);
        }
    }

    public function InterestSave(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver     = $resolver ?? app(ReceiptTypeResolver::class);
        $amount       = $request->input('amount');
        $receiptValue = $request->input('search_receipt_no');

        if (!$amount || !is_numeric($amount)) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        $activeRates = $resolver->getActiveTypes()->reject(fn ($type) => strtoupper((string) $type->receiptname) === 'SILVER');
        $rate1 = $activeRates->where('pawn_amount', '>=', 100000)->value('rate3');
        $rate2 = $activeRates->whereBetween('pawn_amount', [50000, 99999])->value('rate3');
        $rate3 = $activeRates->where('pawn_amount', '<', 50000)->value('rate3');

        if ($amount >= 100000) {
            $interestRate = $rate1;
        } elseif ($amount >= 50000 && $amount <= 99999) {
            $interestRate = $rate2;
        } else {
            $interestRate = $rate3;
        }

        if ($receiptValue >= 100000) {
            $interestRatetwo = $rate1;
        } elseif ($receiptValue >= 50000 && $receiptValue <= 99999) {
            $interestRatetwo = $rate2;
        } else {
            $interestRatetwo = $rate3;
        }

        if (!$interestRate || !$interestRatetwo) {
            return response()->json(['error' => 'No interest rate found for this amount'], 404);
        }

        $ValiledPeriod  = ($receiptValue / 100) * $interestRatetwo;
        $InterestAmount = ($amount / 100) * $interestRate;

        return response()->json([
            'amount'          => $amount,
            'interestRatetwo' => $interestRatetwo,
            'interestRate'    => $interestRate,
            'ValiledPeriod'   => $ValiledPeriod,
            'InterestAmount'  => $InterestAmount,
            'receiptData'     => $receiptValue
        ]);
    }

    public function getCustomerDetailsdata(Request $request)
    {
        $receiptNo   = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $dataCustomer = TPawnSum::where('Receipt_Number', $receiptNo)
                                ->where('BC', $branch_code)
                                ->first();

        if (!$dataCustomer) {
            return response()->json([
                'status' => 'not_found'
            ]);
        }

        $data = TPawnSum::where('Customer_NIC', $dataCustomer->Customer_NIC)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    public function getCustomerDetails(Request $request, ReceiptHistoryService $historyService)
    {
        $searchValue = trim((string) ($request->search_receipt_no ?? ''));
        $branch_code = auth()->user()->BC;

        if (empty($searchValue)) {
            return response()->json(['status' => 'not_found', 'data' => []]);
        }

        if ($request->boolean('payment_workflow') && !\App\Services\ReceiptPaymentEligibility::query('Pawn', $branch_code)->where(function ($q) use ($searchValue) {
            $q->where('Receipt_Number', $searchValue)
              ->orWhere('Ticket_Number', $searchValue)
              ->orWhere('Invoice_Number', $searchValue);
        })->exists()) {
            return response()->json(['status' => 'not_found', 'data' => []]);
        }

        $receipt = TPawnSum::where('BC', $branch_code)
            ->where(function ($q) use ($searchValue) {
                $q->where('Receipt_Number', $searchValue)
                  ->orWhere('Ticket_Number', $searchValue)
                  ->orWhere('Invoice_Number', $searchValue);
            })
            ->first();

        if (!$receipt) {
            return response()->json(['status' => 'not_found', 'data' => []]);
        }

        $data = $historyService->paymentLedger($receipt);

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 'not_found'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }
}
