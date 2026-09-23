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
use App\Models\TCustomerAccount;
use App\Models\TPawnTrans;
use App\Models\MPawnfeedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptTypeResolver;


class RedeemController extends Controller
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

        return view('RedeemReceipt')
        ->with("branchDetails", $branchData)
        ->with("maxRedeem", $maxRedeemNos);
    }

    //receipt Search function
    public function search(Request $request, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $query = TPawnSum::where('Receipt_Number',$receiptNo)
                        ->where('BC',$branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0);
        if (Schema::hasColumn('t_pawn_sums', 'is_blocked')) $query->where('is_blocked', 0);
        $dataTPawnSum = $query->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->get();

            $calculationReceipt = $resolver->receiptForCalculation($data->first());
            $receipt_data = collect([$resolver->resolveForCurrentCycle($data->first())]);

            $maxRedeemNo = TRedeemSum::orderBy('Redeem_Number', 'desc')
            ->value('Redeem_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";


            $MPawnfeedback = MPawnfeedback::where('Receipt_Number',$receiptNo)
                       ->where('BC',$branch_code)
                       ->get();

            return view('redeemSearch')
            ->with("maxRedeem", $maxRedeemNos)
            ->with('customerData', $cus_data)
            ->with('receiptTypeData', $receipt_data)
            ->with('MPawnfeedback', $MPawnfeedback)
            ->with('pawnType', $pawn_type)
            ->with('receiptData', collect([$calculationReceipt]))
            ->with('financial', $calculator->calculate($calculationReceipt));
        }else{
            return response()->json([
                'status' => Schema::hasColumn('t_pawn_sums', 'is_blocked') && TPawnSum::where('Receipt_Number', $receiptNo)->where('BC', $branch_code)
                    ->where('IsRedeemed', 0)->where('isForfeit', 0)->where('is_blocked', 1)->exists()
                    ? 'blocked' : 'not_found'
            ]);
        }
    }

    //invoice Search function
    public function searchInvoice(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $invoiceNo = $request->search_invoice_no;
        $branch_code = auth()->user()->BC;

        $query = TOpeningPawnSum::where('Invoice_Number',$invoiceNo)
                        ->where('BC',$branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0);
        if (Schema::hasColumn('t_opening_pawn_sums', 'is_blocked')) $query->where('is_blocked', 0);
        $TOpeningPawnSumdata = $query->get();

        $data = $TOpeningPawnSumdata;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;

            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC',$branch_code)
                        ->get();

            $calculationReceipt = $resolver->receiptForCalculation($data->first());
            $receipt_data = collect([$resolver->resolveForCurrentCycle($data->first())]);

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
                'status' => Schema::hasColumn('t_opening_pawn_sums', 'is_blocked') && TOpeningPawnSum::where('Invoice_Number', $invoiceNo)->where('BC', $branch_code)
                    ->where('IsRedeemed', 0)->where('isForfeit', 0)->where('is_blocked', 1)->exists()
                    ? 'blocked' : 'not_found'
            ]);
        }
    }

    //invoice searchTicket function
    public function searchTicket(Request $request, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $receiptNo = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $query = TPawnSum::where('Ticket_Number',$receiptNo)
                        ->where('BC',$branch_code)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0);
        if (Schema::hasColumn('t_pawn_sums', 'is_blocked')) $query->where('is_blocked', 0);
        $dataTPawnSum = $query->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->where('BC',$branch_code)
                        ->get();

            $calculationReceipt = $resolver->receiptForCalculation($data->first());
            $receipt_data = collect([$resolver->resolveForCurrentCycle($data->first())]);

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
            ->with('receiptData', collect([$calculationReceipt]))
            ->with('financial', $calculator->calculate($calculationReceipt));
        }else{
            return response()->json([
                'status' => Schema::hasColumn('t_pawn_sums', 'is_blocked') && TPawnSum::where('Ticket_Number', $receiptNo)->where('BC', $branch_code)
                    ->where('IsRedeemed', 0)->where('isForfeit', 0)->where('is_blocked', 1)->exists()
                    ? 'blocked' : 'not_found'
            ]);
        }
    }

    public function create()
    {

    }


public function store(Request $request, ReceiptLifecycleService $lifecycle, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
{
    $resolver = $resolver ?? app(ReceiptTypeResolver::class);
    $request->validate(['receipt_number'=>'required', 'pawn_receipt_type'=>'required|in:Pawn,Opening_Pawn']);
    DB::beginTransaction();

    try {
        $activeReceipt = \App\Services\ReceiptPaymentEligibility::lockForRedemption($request->pawn_receipt_type, $request->receipt_number, auth()->user()->BC);
        if ($request->pawn_receipt_type === 'Pawn') {
            $financial = $calculator->calculate(
                $resolver->receiptForCalculation($activeReceipt),
                $request->redeem_date
            );
            $interestDays = $financial['days'];
            $request->merge([
                'document_charges' => $financial['service_charge'],
                'Postage_Charges' => $financial['letter_charge'],
                'payable_interest' => $financial['arrears_total'],
            ]);
        }
        $NewRedeem = new TRedeemSum;
        $NewRedeem->Receipt_Number = $request->receipt_number;
        $NewRedeem->Invoice_Number = $request->invoice_number;
        $NewRedeem->Ticket_Number = $request->ticket_number;
        $NewRedeem->Pawn_Receipt_Type = $request->pawn_receipt_type;
        $NewRedeem->Redeem_Date = $request->redeem_date;
        $NewRedeem->Redeem_Number = $request->redeem_no;
        $NewRedeem->Total_Weight = $request->sum_total_weight;
        $NewRedeem->Pawn_Weight = $request->sum_pawn_weight;
        $NewRedeem->Original_Pawn_Amount = $request->pawn_amount_new;
        $NewRedeem->Payable_Pawn_Amount = $request->original_pawn_amount;
        $NewRedeem->Paid_Interest = $request->paid_interest;
        $NewRedeem->Payable_Interest = $request->payable_interest;
        $NewRedeem->Stamp_Fee = $request->stamp_fee;
        $NewRedeem->Document_Charges = $request->document_charges;
        $NewRedeem->Advance_Balance = $request->advance_balance;
        $NewRedeem->Discount = $request->redeem_discount;
        $NewRedeem->Payable_Total = $request->payable_total;
        $NewRedeem->Customer_Name = $request->Customer_Name;
        $NewRedeem->Customer_NIC = $request->Customer_NIC;
        $NewRedeem->Postage_Charges = $request->Postage_Charges;
        $NewRedeem->Stamp_Fee = $request->stampduty;
        $NewRedeem->OC = auth()->user()->username;
        $NewRedeem->BC = auth()->user()->BC;
        $NewRedeem->save();

        $TPawnTrans = new TPawnTrans;
        $TPawnTrans->Customer_NIC = $request->Customer_NIC;
        $TPawnTrans->Customer_Name = $request->Customer_Name;
        $TPawnTrans->code = $request->receipt_number;
        $TPawnTrans->trans_no = $request->redeem_no;
        $TPawnTrans->trans_type = "REDEEM";
        $TPawnTrans->trans_amount = $request->original_pawn_amount;
        $TPawnTrans->dDate = $request->redeem_date;
        $TPawnTrans->Cr_amount = 0;
        $TPawnTrans->Dr_amount = $request->payable_total;
        $TPawnTrans->Paided_Interest = $request->paid_interest;
        $TPawnTrans->OC = auth()->user()->username;
        $TPawnTrans->BC = auth()->user()->BC;
        $TPawnTrans->save();

        $CustomerAccount = new TCustomerAccount();
        $CustomerAccount->customer_id = $request->Customer_NIC;
        $CustomerAccount->Receipt_Date = $request->redeem_date;
        $CustomerAccount->Receipt_Number = $request->redeem_no;
        $CustomerAccount->Pawning_Number = $request->receipt_number;
        $CustomerAccount->Payment_amount = $request->payable_total;
        $CustomerAccount->Paid_Interest = $request->paid_interest;
        $CustomerAccount->Balance_Interest_amount = $request->BalanceInterest;
        $CustomerAccount->Paided_Captial_amount = $request->advance_payment;
        $CustomerAccount->Balance_captial_amount = $request->advance_payment;
        $CustomerAccount->trans_Type = "REDEEM_PAYMENT";
        $CustomerAccount->OC = auth()->user()->username;
        $CustomerAccount->BC = auth()->user()->BC;
        $CustomerAccount->save();

        // ===== ACCOUNTING ENTRIES FOR PAWN REDEMPTION =====
        // When customer redeems, multiple accounting entries are needed:
        // 1. Cash received (Debit Cash)
        // 2. Pawn loan settled (Credit Pawn Loans)
        // 3. Interest income earned (Credit Interest Income)
        // 4. Fees collected (Credit Fee Income)

        $branch_code = auth()->user()->BC;
        $receiptInput_no = $request->receipt_number;
        $Pawn_Receipt_Type = $request->pawn_receipt_type;
        $Payable_Interest = $request->paid_interest;
        $Redeem_Date = $request->redeem_date;

        // Configure these account IDs based on your chart of accounts
        $cashAccountId = 2;              // Cash account
        $pawnLoansAccountId = 1;         // Pawn Loans account
        $interestIncomeAccountId = 3;    // Interest Income account
        $feeIncomeAccountId = 4;         // Fee Income account (for stamp fee, document charges, etc.)

        // Calculate total fees (stamp fee + document charges + postage charges)
        $totalFees = ($request->stampduty ?? 0) +
                     ($request->document_charges ?? 0) +
                     ($request->Postage_Charges ?? 0);

        // Apply discount if any
        $discount = $request->redeem_discount ?? 0;

        // 1. Debit: Cash Account (Asset increases - cash received from customer)
        DB::table('t_account_trans')->insert([
            'trans_date' => $request->redeem_date,
            'voucher_no' => 'RDM-' . $request->invoice_number,
            'account_id' => $cashAccountId,
            'related_id' => $request->redeem_no,
            'related_type' => 'REDEEM',
            'dr' => $request->payable_total,
            'Invoice_no' => $request->invoice_number,
            'cr' => 0,
            'description' => 'Cash received for redemption of pawn receipt #' . $request->invoice_number . ' - ' . $request->Customer_Name,
            'branch_code' => $branch_code,
            'created_by' => auth()->user()->username,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        // 5. If there's a discount given, debit Discount Expense
        if ($discount > 0) {
            $discountExpenseAccountId = 5; // Configure this account ID
            DB::table('t_account_trans')->insert([
                'trans_date' => $request->redeem_date,
                'voucher_no' => 'RDM-' . $request->redeem_no,
                'account_id' => $discountExpenseAccountId,
                'related_id' => $request->redeem_no,
                'Invoice_no' => $request->invoice_number,
                'related_type' => 'REDEEM',
                'dr' => $discount,
                'cr' => 0,
                'description' => 'Discount allowed on redemption - Receipt #' . $request->receipt_number,
                'branch_code' => $branch_code,
                'created_by' => auth()->user()->username,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        // ===== END ACCOUNTING ENTRIES =====

        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode', $branch_code)->paginate(1);

        if ($Pawn_Receipt_Type == "Pawn") {
            TPawnSum::where('Receipt_Number', $request->receipt_number)
                ->where('BC', $branch_code)
                ->update([
                    'IsRedeemed' => 1,
                    'Redeem_interest' => $Payable_Interest,
                    'Redeem_Date' => $Redeem_Date,
                ]);

            $redeemedReceipt = TPawnSum::where('Receipt_Number', $request->receipt_number)
                ->where('BC', $branch_code)->firstOrFail();
            \App\Services\ReceiptArticleStatus::sync($redeemedReceipt);
            $lifecycle->closeForRedemption($redeemedReceipt, (float) $request->payable_total);

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

            $pdf = PDF::loadView('redeemReceiptPrint', [
                'interestDays' => $interestDays,
                'redeemdata' => $T_redeemdata,
                'pawnSumData' => $T_sumdata,
                'pawnDetailsData' => $T_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData
            ]);

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
            \App\Services\ReceiptArticleStatus::sync($T_open_sumdata->first(), 'Opening_Pawn');

            $T_open_detailsdata = TOpeningPawnDetails::where('Receipt_Number', $receiptInput_no)
                ->where('BC', $branch_code)
                ->get();

            $pdf = PDF::loadView('redeemReceiptPrint', [
                'interestDays' => \App\Services\ReceiptInterestPeriod::days($T_open_sumdata->first(), $request->redeem_date),
                'redeemdata' => TRedeemSum::where('Redeem_Number', $request->redeem_no)->where('Pawn_Receipt_Type', $request->pawn_receipt_type)->where('BC', $branch_code)->get(),
                'pawnSumData' => $T_open_sumdata,
                'pawnDetailsData' => $T_open_detailsdata,
                'branchDetails' => $branchData,
                'companyData' => $companyData
            ]);

            $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
            $pdf->save($pdfPath);
        }

        $pdfUrl = asset('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');

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
