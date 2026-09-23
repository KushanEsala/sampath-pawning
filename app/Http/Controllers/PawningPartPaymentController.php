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
use App\Models\TPawnTrans;
use Carbon\Carbon;
use App\Models\MPawnfeedback;
use Illuminate\Support\Facades\DB;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptLifecycleService;
use App\Services\PartPaymentCalculator;
use Illuminate\Validation\ValidationException;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptTypeResolver;


class PawningPartPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
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
      public function PartpaymentSearch(Request $request, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $receiptNo = $request->search_receipt_no;
        $field = $request->has('search_ticket_no') ? 'Ticket_Number' : ($request->has('search_invoice_no') ? 'Invoice_Number' : 'Receipt_Number');
        $searchValue = $field === 'Ticket_Number' ? $request->search_ticket_no : ($field === 'Invoice_Number' ? $request->search_invoice_no : $receiptNo);
        $branchReceipt = $request->branch;
        $branch_code = auth()->user()->BC;

        $dataTPawnSum = TPawnSum::where($field, $searchValue)
                        ->where('IsRedeemed', 0)
                        ->where('isForfeit', 0)
                         ->where('BC',  $branch_code)
                        ->get();

        $data = $dataTPawnSum;

        if($data->count()>0){
            $receiptNo = $data->first()->Receipt_Number;
            $branch_code = auth()->user()->BC;
            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)
                        ->get();

            $calculationReceipt = $resolver->receiptForCalculation($data->first());
            $receipt_data = collect([$resolver->resolveForCurrentCycle($data->first())]);

            $maxRedeemNo = TPawnPayment::where('BC',$branch_code)
            ->orderBy('Redeem_Number', 'desc')
            ->value('Redeem_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Pawn";

            $MPawnfeedback = MPawnfeedback::where('Receipt_Number',$receiptNo)
                       ->where('BC',$branch_code)
                       ->get();

            return view('searchPartPayment')
            ->with("maxRedeem", $maxRedeemNos)
            ->with('customerData', $cus_data)
                ->with('MPawnfeedback', $MPawnfeedback)
            ->with('receiptTypeData', $receipt_data)
            ->with('pawnType', $pawn_type)
            ->with('receiptData', collect([$calculationReceipt]))
            ->with('financial', $calculator->calculate($calculationReceipt));
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }
    public function PartpaymentTicketSearch(Request $request, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
    {
        $request->merge(['search_ticket_no'=>$request->search_receipt_no]);
        return $this->PartpaymentSearch($request, $calculator, $resolver);
    }

    public function PartpaymentInvoiceSearch(Request $request, ReceiptFinancialCalculator $calculator, ?ReceiptTypeResolver $resolver = null)
    {
        return $this->PartpaymentSearch($request, $calculator, $resolver);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
 public function AddPartPayment(Request $request, ReceiptFinancialCalculator $calculator, ReceiptLifecycleService $lifecycle, ?ReceiptTypeResolver $resolver = null)
 {
     $resolver = $resolver ?? app(ReceiptTypeResolver::class);
     $request->validate(['receipt_number'=>'required', 'pawn_receipt_type'=>'required|in:Pawn']);
     DB::beginTransaction();
 
     try {
        $branch_code = auth()->user()->BC;
        $activeReceipt = \App\Services\ReceiptPaymentEligibility::lock('Pawn', $request->receipt_number, $branch_code);
        $request->validate([
            'redeem_date' => 'required|date',
            'interest_Paid' => 'required|numeric|min:0.01',
            'redeem_discount' => 'nullable|numeric|min:0',
            'validyed_type' => 'required|integer|min:1|max:12',
        ]);
        $hasArrearsLetters = (bool) ($activeReceipt->is_letter_1 || $activeReceipt->is_letter_2 || $activeReceipt->is_letter_3);
        $calculationReceipt = $resolver->receiptForCalculation($activeReceipt);
        $financial = $calculator->calculate($calculationReceipt, $request->redeem_date);
        // Capture the period before part payment advances the interest start date.
        $interestDays = $financial['days'];
        $currentType = $resolver->resolveForCurrentCycle($activeReceipt);
        $currentPrincipal = (float) ($activeReceipt->Pawn_Amount ?: $activeReceipt->Amount ?: 0);
        $discount = (float) ($request->redeem_discount ?? 0);
        $stampFee = (float) ($currentType->stampduty ?? 0);
        $interestDue = (float) $financial['interest'];
        $serviceCharge = (float) $financial['service_charge'];
        $letterCharge = (float) $financial['letter_charge'];
        $allocation = app(PartPaymentCalculator::class)->calculate(
            $currentPrincipal,
            $interestDue,
            $serviceCharge,
            $letterCharge,
            $stampFee,
            (float) $request->interest_Paid,
            $discount
        );
        $chargesDue = $allocation['charges_due'];
        $paymentReceived = $allocation['payment_received'];
        $redemptionTotal = $allocation['redemption_total'];
        $paidCharges = $allocation['paid_charges'];
        $paidInterest = $allocation['paid_interest'];
        $principalPaid = $allocation['principal_paid'];
        $newPrincipal = $allocation['new_principal'];
        $unpaidCharges = $allocation['unpaid_charges'];

        if ($paymentReceived + 0.01 >= $redemptionTotal) {
            throw ValidationException::withMessages([
                'interest_Paid' => 'Use Redeem Receipt when the full redemption amount is paid.',
            ]);
        }

        if ($newPrincipal > 0 && $newPrincipal < 1000) {
            throw ValidationException::withMessages([
                'interest_Paid' => 'A minimum remaining capital balance of Rs. 1,000.00 is required.',
            ]);
        }

        $request->merge([
            'document_charges' => $serviceCharge,
            'Postage_Charges' => $letterCharge,
            'stamp_fee' => $stampFee,
            'paid_interest' => $paidInterest,
            'advance_payment' => $principalPaid,
            'Payable_Pawn_Amount' => $newPrincipal,
            'BalanceInterest' => $unpaidCharges,
            'payable_total' => $paymentReceived,
            'redeem_total' => $redemptionTotal,
            'current_pawn_amount' => $currentPrincipal,
            'original_pawn_amount' => $currentPrincipal,
            'PayTotalAmount' => $paidCharges,
        ]);
        $requiredArrears = $hasArrearsLetters ? $financial['arrears_total'] : 0;
        $receivedAmount = $paymentReceived;
        if ($hasArrearsLetters && $receivedAmount + 0.01 < $requiredArrears) {
            throw ValidationException::withMessages([
                'payable_total' => 'Full arrears payment of Rs. '.number_format($requiredArrears, 2).' is required to reactivate this receipt.',
            ]);
        }

        $NewRedeem = new TPawnPayment;
            $NewRedeem->Receipt_Number = $request->receipt_number;
            $NewRedeem->Invoice_Number = $activeReceipt->Invoice_Number;
            $NewRedeem->Ticket_Number = $activeReceipt->Ticket_Number;
            $NewRedeem->Customer_Name = $activeReceipt->Customer_Name;
            $NewRedeem->Customer_NIC = $activeReceipt->Customer_NIC;
            $NewRedeem->Pawn_Receipt_Type = $request->pawn_receipt_type;
            $NewRedeem->Redeem_Date = $request->redeem_date;
            $NewRedeem->Redeem_Number = $request->redeem_no;
            $NewRedeem->Total_Weight = $activeReceipt->Total_Weight;
            $NewRedeem->Pawn_Weight = $activeReceipt->Pawn_Weight;
            $NewRedeem->Original_Pawn_Amount = $currentPrincipal;
            $NewRedeem->Payable_Pawn_Amount = $newPrincipal;
            $NewRedeem->Paid_Interest = $paidInterest;
            $NewRedeem->Payable_Interest = $paidCharges;
            $NewRedeem->Stamp_Fee = $stampFee;
            $NewRedeem->Document_Charges = $serviceCharge;
            $NewRedeem->Postage_Charges = $letterCharge;
            $NewRedeem->paid_cap_amount = $redemptionTotal;
            $NewRedeem->Advance_Balance = $request->advance_balance;
            $NewRedeem->Advance_Payment = $principalPaid;
            $NewRedeem->Discount = $discount;
            $NewRedeem->Payable_Total = $paymentReceived;
            $NewRedeem->current_pawn_amount = $currentPrincipal;
            $NewRedeem->OC = auth()->user()->username;
            $NewRedeem->BC = auth()->user()->BC;
            $NewRedeem->save();


        $receiptInput_no = $request->receipt_number;
        $Pawn_Receipt_Type = $request->pawn_receipt_type;

        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode', $branch_code)->paginate(1);

        $receipt_advanceAmount = $principalPaid;
        $validyed_type = (int) $request->validyed_type;
        $pawndate = Carbon::parse($request->redeem_date)->addDay();
        $isSilver = strtoupper((string) ($activeReceipt->receiptname ?: $activeReceipt->Receipt_Type)) === 'SILVER';
        $final_date_string = Carbon::parse($request->redeem_date)->addMonths(max(1, $validyed_type))->toDateString();
        $interest_Balance = $unpaidCharges;

        // Create a new Part Payment transaction
        $TPawnTrans = new TPawnTrans;
        $TPawnTrans->Customer_NIC = $activeReceipt->Customer_NIC;
        $TPawnTrans->Customer_Name = $activeReceipt->Customer_Name;
        $TPawnTrans->code = $request->receipt_number;
        $TPawnTrans->trans_no = $request->redeem_no;
        $TPawnTrans->trans_type = "PART_PAYMENT";
        $TPawnTrans->trans_amount = $request->redeem_total;
        $TPawnTrans->dDate = $request->redeem_date;
        $TPawnTrans->Cr_amount = 0;
        $TPawnTrans->Dr_amount = $request->payable_total;
        $TPawnTrans->Paided_Interest = $paidInterest;
        $TPawnTrans->Pawn_Amount = $currentPrincipal;
        $TPawnTrans->payable_total = $request->payable_total;
        $TPawnTrans->Paided_Captional = $principalPaid;
        $TPawnTrans->Extend_Date = $final_date_string;
        $TPawnTrans->interest_Balance = $interest_Balance;
        $TPawnTrans->OC = auth()->user()->username;
        $TPawnTrans->BC = auth()->user()->BC;
        $TPawnTrans->save();

        /* =========================
           ACCOUNTING ENTRIES FOR PART PAYMENT
        ==========================*/
        // In part payment, customer pays:
        // 1. Interest (paid_interest)
        // 2. Principal amount (advance_payment)
        // 3. Fees (stamp_fee + document_charges)
        // Total payment received = payable_total

        // Configure these account IDs based on your chart of accounts
        $cashAccountId = 2;              // Cash account
        $pawnLoansAccountId = 1;         // Pawn Loans account
        $interestIncomeAccountId = 3;    // Interest Income account
        $feeIncomeAccountId = 4;         // Fee Income account

        $totalPayment = $paymentReceived;
        $interestPaid = $paidInterest;
        $totalFees = $stampFee + $serviceCharge + $letterCharge;

        // 1. Debit: Cash Account (Total payment received from customer)
        DB::table('t_account_trans')->insert([
            'trans_date' => $request->redeem_date,
                'voucher_no' => 'PP-' . $activeReceipt->Invoice_Number,
            'account_id' => $cashAccountId,
            'related_id' => $request->redeem_no,
            'related_type' => 'PART_PAYMENT',
              'Invoice_no' => $activeReceipt->Invoice_Number,
            'dr' => $totalPayment,
            'cr' => 0,
            'description' => 'Part payment received for receipt #' . $activeReceipt->Receipt_Number,
            'branch_code' => $branch_code,
            'created_by' => auth()->user()->username,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        /* =========================
           END ACCOUNTING ENTRIES
        ==========================*/

        $paymentDate = $request->redeem_date ?? now()->toDateString();
        $isSilver = strtoupper((string) ($activeReceipt->receiptname ?: $activeReceipt->Receipt_Type)) === 'SILVER';
        $rateRow = $resolver->resolveForRepawnAmount($newPrincipal, $paymentDate, $isSilver)
            ?? $currentType;
        $receiptname = $isSilver ? 'SILVER' : ($rateRow->receiptname ?? $activeReceipt->Receipt_Type);
        $interestRate = $isSilver
            ? (float) ($rateRow->rate1 ?? $activeReceipt->rate1 ?? 0)
            : (float) ($rateRow->rate3 ?? $activeReceipt->rate3 ?? 0);

        $updateData = [
            'Pawn_Amount' => $newPrincipal,
            'RePawning_amount' => $newPrincipal,
            'Advance_Payment' => $receipt_advanceAmount,
            // The old interest period ends at this payment. Only genuinely
            // unpaid charges carry into the next period.
            'interest_Paid' => 0,
            'BalanceInterest' => $unpaidCharges,
            'Pawn_Date' => $pawndate,
            'Valid_Period' => $validyed_type,
            'Final_date' => $final_date_string,
            'RePawning_date' => $pawndate,
            'Receipt_Type' => $receiptname,
            'receiptname' => $receiptname,
            'Interest_Rate' => $interestRate,
        ];

        if ($rateRow) {
            $updateData = array_merge($updateData, [
                'rate1' => $rateRow->rate1,
                'rate2' => $rateRow->rate2,
                'rate3' => $rateRow->rate3,
                'period1' => $rateRow->period1,
                'period2' => $rateRow->period2,
                'period3' => $rateRow->period3,
                'validPeriod' => $rateRow->validPeriod,
                'service_charge' => $rateRow->service_charge,
                'Postage_charge' => $rateRow->Postage_charge,
                's_charge_less' => $rateRow->s_charge_less,
                's_charge_greater' => $rateRow->s_charge_greater,
                'letter_1_days' => $rateRow->letter_1_days ?? 21,
                'letter_2_days' => $rateRow->letter_2_days ?? 21,
                'letter_3_days' => $rateRow->letter_3_days ?? 21,
                'forfeit_reminder_days' => $rateRow->forfeit_reminder_days ?? 21,
            ]);
        }

        TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)
            ->update($updateData);

        if ($hasArrearsLetters) {
            $lifecycle->reactivate(
                TPawnSum::where('id', $activeReceipt->id)->firstOrFail(),
                $receivedAmount,
                'Full arrears paid through part payment; default month-based expiry applied.'
            );
        }

        $T_sumdata = TPawnSum::where('Receipt_Number', $receiptInput_no)
            ->where('BC', $branch_code)
            ->get();

        $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)
            ->where('BC', $branch_code)
            ->get();

        $T_redeemdata = TPawnPayment::where('Redeem_Number', $request->redeem_no)
            ->where('Pawn_Receipt_Type', $request->pawn_receipt_type)
            ->where('BC', $branch_code)
            ->get();

        // Generate the PDF content using a view
        $pdf = PDF::loadView('partpaymentReceiptPrint', [
            'interestDays' => $interestDays,
            'pawnSumData' => $T_sumdata,
            'pawnDetailsData' => $T_detailsdata,
            'redeemdata' => $T_redeemdata,
            'branchDetails' => $branchData,
            'companyData' => $companyData
        ]);

        $pdfPath = storage_path('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');
        $pdf->save($pdfPath);
        $pdfUrl = asset('../public/assets/pdf/Redeem_receipt' . $branch_code . '.pdf');

        \App\Services\ReceiptArticleStatus::sync($activeReceipt->fresh());
        DB::commit();

        return back()
            ->with('done', 'The Part Payment has been added')
            ->with("pdfLink", $pdfUrl);

    } catch (ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
    } catch (\Exception $e) {
        DB::rollBack();

        return back()
            ->with('error', 'An error occurred: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function PartpaymentHistory(Request $request, ReceiptHistoryService $historyService)
    {
        $searchValue = trim($request->search_receipt_no ?? '');
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

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
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
