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
use App\Models\TRepawningSum;
use App\Models\TOpeningPawnSum;
use App\Models\TOpeningPawnDetails;
use App\Models\branchDel;
use Carbon\Carbon;
use App\Models\TPawnTrans;
use App\Models\MPawnfeedback;
use Illuminate\Support\Facades\DB;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptLifecycleService;
use App\Services\RepawningCalculator;
use App\Services\ReceiptTypeResolver;
use Illuminate\Validation\ValidationException;


class RepawningController extends Controller
{

    public function index()
    {
        $branch_code = auth()->user()->BC;
        $maxRedeemNo = TRepawningSum::where('BC',$branch_code)
        ->orderBy('Redeem_Number', 'desc')
        ->value('Redeem_Number');
        $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

        $branchData = branchDel::where('bccode',$branch_code)
                    ->paginate(1);

        return view('repawning')
        ->with("branchDetails", $branchData)
        ->with("maxRedeem", $maxRedeemNos);
    }

    // ─────────────────────────────────────────────────────────────
    // Receipt Search function
    // ─────────────────────────────────────────────────────────────
    public function search(
        Request $request,
        ?ReceiptFinancialCalculator $calculator = null,
        ?ReceiptTypeResolver $resolver = null,
        ?RepawningCalculator $repawningCalculator = null
    )
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $repawningCalculator = $repawningCalculator ?? app(RepawningCalculator::class);
        $receiptNo   = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $receipt = TPawnSum::where('Receipt_Number', $receiptNo)
            ->where('IsRedeemed', 0)
            ->where('isForfeit', 0)
            ->where('BC', $branch_code)
            ->first();

        if (!$receipt) {
            return response()->json(['status' => 'not_found']);
        }

        return $this->renderSearchResult($receipt, $calculator, $resolver, $repawningCalculator);
    }

    // ─────────────────────────────────────────────────────────────
    // Invoice Search function
    // ─────────────────────────────────────────────────────────────
    public function searchInvoice(
        Request $request,
        ?ReceiptFinancialCalculator $calculator = null,
        ?ReceiptTypeResolver $resolver = null,
        ?RepawningCalculator $repawningCalculator = null
    )
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $repawningCalculator = $repawningCalculator ?? app(RepawningCalculator::class);
        $invoiceNo   = $request->search_invoice_no;
        $branch_code = auth()->user()->BC;

        $receipt = TPawnSum::where('Invoice_Number', $invoiceNo)
            ->where('IsRedeemed', 0)
            ->where('isForfeit', 0)
            ->where('BC', $branch_code)
            ->first();

        if (!$receipt) {
            return response()->json(['status' => 'not_found']);
        }

        return $this->renderSearchResult($receipt, $calculator, $resolver, $repawningCalculator);
    }

    // ─────────────────────────────────────────────────────────────
    // Ticket Search function
    // ─────────────────────────────────────────────────────────────
    public function searchTicket(
        Request $request,
        ?ReceiptFinancialCalculator $calculator = null,
        ?ReceiptTypeResolver $resolver = null,
        ?RepawningCalculator $repawningCalculator = null
    )
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $repawningCalculator = $repawningCalculator ?? app(RepawningCalculator::class);
        $receiptNo   = $request->search_receipt_no;
        $branch_code = auth()->user()->BC;

        $receipt = TPawnSum::where('Ticket_Number', $receiptNo)
            ->where('IsRedeemed', 0)
            ->where('isForfeit', 0)
            ->where('BC', $branch_code)
            ->first();

        if (!$receipt) {
            return response()->json(['status' => 'not_found']);
        }

        return $this->renderSearchResult($receipt, $calculator, $resolver, $repawningCalculator);
    }

    private function renderSearchResult(
        TPawnSum $receipt,
        ReceiptFinancialCalculator $calculator,
        ReceiptTypeResolver $resolver,
        RepawningCalculator $repawningCalculator
    ) {
        $branchCode = auth()->user()->BC;
        $currentType = $resolver->resolveForCurrentCycle($receipt);
        $calculationReceipt = $resolver->receiptForCalculation($receipt);
        $receiptData = collect([$calculationReceipt]);
        $financial = $calculator->calculate($calculationReceipt);
        $pawnDetails = TPawnDetails::where('Receipt_Number', $receipt->Receipt_Number)
            ->where('BC', $branchCode)
            ->get();
        $karatages = $pawnDetails->pluck('Karatage')->unique()->filter()->values();
        $karatageData = karatage::whereIn('descrption', $karatages)->get();
        $isSilver = strtoupper((string) ($receipt->receiptname ?: $receipt->Receipt_Type)) === 'SILVER';
        $articleValue = $repawningCalculator->preview(
            $receipt,
            $pawnDetails,
            $karatageData,
            $financial,
            $currentType,
            $currentType
        )['article_value'];
        $repawnType = $resolver->resolveForRepawnAmount($articleValue, now(), $isSilver) ?? $currentType;
        $preview = $repawningCalculator->preview(
            $receipt,
            $pawnDetails,
            $karatageData,
            $financial,
            $currentType,
            $repawnType
        );
        $maxRedeemNo = TRepawningSum::where('BC', $branchCode)->max('Redeem_Number');

        return view('repawningsearch', [
            'receiptType' => $resolver->getActiveTypes(),
            'maxRedeem' => str_pad((string) ($maxRedeemNo ?? 0), 4, '0', STR_PAD_LEFT),
            'MPawnfeedback' => MPawnfeedback::where('Receipt_Number', $receipt->Receipt_Number)
                ->where('BC', $branchCode)
                ->get(),
            'customerData' => Customer::where('NIC', $receipt->Customer_NIC)->get(),
            'receiptTypeData' => collect([$currentType]),
            'pawnType' => 'Pawn',
            'receiptData' => $receiptData,
            'pawnDetails' => $pawnDetails,
            'karatageData' => $karatageData,
            'financial' => $financial,
            'repawningPreview' => $preview,
        ]);
    }


    // ─────────────────────────────────────────────────────────────
    // Store Repawning Summary
    // ─────────────────────────────────────────────────────────────
public function StoreRepawningSum(Request $request, ?ReceiptFinancialCalculator $calculator = null, ?ReceiptLifecycleService $lifecycle = null, ?ReceiptTypeResolver $resolver = null)
{
    $calculator = $calculator ?? app(ReceiptFinancialCalculator::class);
    $lifecycle  = $lifecycle ?? app(ReceiptLifecycleService::class);
    $resolver   = $resolver ?? app(ReceiptTypeResolver::class);

    $request->validate([
        'receipt_number'        => 'required|string',
        'invoice_number'        => 'required|string',
        'ticket_number'         => 'required|string',
        'pawn_receipt_type'     => 'required|string',
        'redeem_date'           => 'required|date',
        'redeem_no'             => 'required|string',
        'sum_total_weight'      => 'required|numeric',
        'sum_pawn_weight'       => 'required|numeric',
        'original_pawn_amount'  => 'required|numeric',
        'payable_total'         => 'required|numeric|min:0',
        'validyed_type'         => 'required|integer|min:1|max:12',
        'redeem_discount'       => 'nullable|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        $branch_code = auth()->user()->BC;
        $username    = auth()->user()->username;

        /* =========================
           CHECKBOX: document charges
           Only apply if checkbox ticked
        ==========================*/
        $existingPawn = TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)->where('IsRedeemed', 0)->where('isForfeit', 0)
            ->lockForUpdate()->firstOrFail();
        $duplicateRepawn = TRepawningSum::where('BC', $branch_code)
            ->where('Redeem_Number', $request->redeem_no)
            ->exists();
        if ($duplicateRepawn) {
            throw ValidationException::withMessages([
                'redeem_no' => 'This repawning number has already been saved. Refresh the page before trying again.',
            ]);
        }
        $calculationReceipt = $resolver->receiptForCalculation($existingPawn);
        $financial = $calculator->calculate($calculationReceipt, $request->redeem_date);
        $currentType = $resolver->resolveForCurrentCycle($existingPawn);
        $documentCharges = (float) $financial['service_charge'];
        $letterCharges = (float) $financial['letter_charge'];
        $paidInterest = (float) $financial['interest'];
        $stampFee = (float) ($currentType->stampduty ?? 0);
        $discount = (float) ($request->redeem_discount ?? 0);
        $payableTotal = (float) $request->payable_total;
        $validMonths = (int) $request->validyed_type;
        $isSilverReceipt = strtoupper((string) ($existingPawn->receiptname ?: $existingPawn->Receipt_Type)) === 'SILVER';
        if ($isSilverReceipt) $validMonths = 1;
        $currentPrincipal = (float) ($existingPawn->Pawn_Amount ?: $existingPawn->Amount ?: 0);
        $redeemTotal = max(0, $currentPrincipal + $paidInterest + $documentCharges + $letterCharges + $stampFee - $discount);

        /* =========================
           SAVE REPAWNING SUMMARY
        ==========================*/
        $NewRedeem = new TRepawningSum();
        $NewRedeem->Receipt_Number       = $request->receipt_number;
        $NewRedeem->Invoice_Number       = $existingPawn->Invoice_Number;
        $NewRedeem->Ticket_Number        = $existingPawn->Ticket_Number;
        $NewRedeem->Pawn_Receipt_Type    = $request->pawn_receipt_type;
        $NewRedeem->Redeem_Date          = $request->redeem_date;
        $NewRedeem->Redeem_Number        = $request->redeem_no;
        $NewRedeem->Total_Weight         = $existingPawn->Total_Weight;
        $NewRedeem->Pawn_Weight          = $existingPawn->Pawn_Weight;
        $NewRedeem->Original_Pawn_Amount = $existingPawn->Amount;
        $NewRedeem->Payable_Pawn_Amount  = $currentPrincipal;
        $NewRedeem->Paid_Interest        = $paidInterest;
        $NewRedeem->Payable_Interest     = $request->payable_interest ?? 0;
        $NewRedeem->Stamp_Fee            = $stampFee;
        $NewRedeem->Document_Charges     = $documentCharges;
        $NewRedeem->Advance_Balance      = $request->advance_balance ?? 0;
        $NewRedeem->Discount             = $discount;
        $NewRedeem->Payable_Total        = $payableTotal;
        $NewRedeem->Redeem_total         = $redeemTotal;
        $NewRedeem->validyed_type        = $validMonths;
        $NewRedeem->OC                   = $username;
        $NewRedeem->BC                   = $branch_code;
        $NewRedeem->save();

        /* =========================
           CALCULATIONS
        ==========================*/
        $pawnSum = $existingPawn;
        $hasArrearsLetters = (bool) ($pawnSum->is_letter_1 || $pawnSum->is_letter_2 || $pawnSum->is_letter_3);
        $requiredArrears = $hasArrearsLetters ? $financial['arrears_total'] : 0;
        $arrearsPaid = $paidInterest
            + (float) $letterCharges
            + (float) $documentCharges;

        $totalRepawnPayment = max(0, $currentPrincipal
            + $payableTotal
            + $paidInterest
            + $documentCharges
            + $letterCharges
            + $stampFee
            - $discount);

        /* =========================
           FINAL DATE
        ==========================*/
        $final_date = Carbon::parse($request->redeem_date)->addMonths(max(1, $validMonths))->toDateString();

        /* =========================
           PAWN TRANSACTION
        ==========================*/
        $TPawnTrans = new TPawnTrans();
        $TPawnTrans->Customer_NIC    = $request->Customer_NIC;
        $TPawnTrans->Customer_Name   = $request->Customer_Name;
        $TPawnTrans->code            = $request->receipt_number;
        $TPawnTrans->trans_no        = $request->redeem_no;
        $TPawnTrans->trans_type      = 'REPAWNING';
        $TPawnTrans->trans_amount    = $redeemTotal;
        $TPawnTrans->dDate           = $request->redeem_date;
        $TPawnTrans->Dr_amount       = 0;
        $TPawnTrans->Cr_amount       = $payableTotal;
        $TPawnTrans->Paided_Interest = $paidInterest;
        $TPawnTrans->Pawn_Amount     = $currentPrincipal;
        $TPawnTrans->Extend_Date     = $final_date;
        $TPawnTrans->OC              = $username;
        $TPawnTrans->BC              = $branch_code;
        $TPawnTrans->save();

        /* =========================
           ACCOUNTING ENTRIES
        ==========================*/
        $cashAccountId           = 2;
        $pawnLoansAccountId      = 1;
        $interestIncomeAccountId = 3;
        $feeIncomeAccountId      = 4;

        $totalFees      = $stampFee + $documentCharges + $letterCharges;
        $extraCashGiven = $payableTotal;
        $interestPaid   = $paidInterest;
        $netCashOut     = $extraCashGiven - $interestPaid - $totalFees;

        if ($extraCashGiven > 0) {
            DB::table('t_account_trans')->insert([
                'trans_date'   => $request->redeem_date,
               'voucher_no'   => 'REPAWNING-' . $existingPawn->Invoice_Number,
                'account_id'   => $cashAccountId,
                'related_id'   => $request->redeem_no,
                'related_type' => 'REPAWNING',
                'Invoice_no'   => $existingPawn->Invoice_Number,
                'dr'           => 0,
                'cr'           => $extraCashGiven,
                'description'  => 'Extra cash disbursed on repawning - Receipt #' . $existingPawn->Receipt_Number,
                'branch_code'  => $branch_code,
                'created_by'   => $username,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        /* =========================
           INTEREST RATE LOOKUP
        ==========================*/
        $repawnDate = $request->redeem_date ?? now()->toDateString();
        $isSilver = strtoupper((string) ($existingPawn->receiptname ?: $existingPawn->Receipt_Type)) === 'SILVER';

        if ($isSilver) {
            $rateRow = $resolver->resolveForRepawnAmount($totalRepawnPayment, $repawnDate, true);
            $interestRateName = 'SILVER';
            $interestRate     = $rateRow->rate1 ?? $existingPawn->rate1 ?? 0;
        } else {
            $rateRow = $resolver->resolveForRepawnAmount($totalRepawnPayment, $repawnDate);

            $interestRateName = $rateRow->receiptname ?? null;
            $interestRate     = $rateRow->rate3 ?? 0;
        }

        /* =========================
           UPDATE PAWN SUMMARY
        ==========================*/
        $updateData = [
            'RePawning_amount'   => $totalRepawnPayment,
            'Pawn_Amount'        => $totalRepawnPayment,
            'RePawn_get_amount'  => $payableTotal,
            'RePawning_date'     => $request->redeem_date,
            'Pawn_Date'          => $request->redeem_date,
            'Valid_Period'       => $validMonths,
            'RePawning_interest' => $paidInterest,
            'interest_Paid'      => 0,
            'BalanceInterest'    => 0,
            'Final_date'         => $final_date,
        ];

        if ($isSilver && $rateRow) {
            $updateData['Receipt_Type']  = 'SILVER';
            $updateData['receiptname']   = 'SILVER';
            $updateData['Interest_Rate'] = $interestRate;
            $updateData['rate1']         = $rateRow->rate1;
            $updateData['rate2']         = $rateRow->rate2;
            $updateData['rate3']         = $rateRow->rate3;
            $updateData['period1']       = $rateRow->period1;
            $updateData['period2']       = $rateRow->period2;
            $updateData['period3']       = $rateRow->period3;
            $updateData['validPeriod']   = $rateRow->validPeriod;
            $updateData['service_charge']= $rateRow->service_charge;
            $updateData['Postage_charge']= $rateRow->Postage_charge;
            $updateData['s_charge_less'] = $rateRow->s_charge_less;
            $updateData['s_charge_greater'] = $rateRow->s_charge_greater;
            $updateData['letter_1_days'] = $rateRow->letter_1_days ?? 21;
            $updateData['letter_2_days'] = $rateRow->letter_2_days ?? 21;
            $updateData['letter_3_days'] = $rateRow->letter_3_days ?? 21;
            $updateData['forfeit_reminder_days'] = $rateRow->forfeit_reminder_days ?? 21;
        } elseif ($rateRow) {
            $updateData['Interest_Rate'] = $interestRate;
            if ($interestRateName) {
                $updateData['Receipt_Type'] = $interestRateName;
                $updateData['receiptname']  = $interestRateName;
            }
            $updateData['rate1']   = $rateRow->rate1;
            $updateData['rate2']   = $rateRow->rate2;
            $updateData['rate3']   = $rateRow->rate3;
            $updateData['period1'] = $rateRow->period1;
            $updateData['period2'] = $rateRow->period2;
            $updateData['period3'] = $rateRow->period3;
            $updateData['validPeriod']   = $rateRow->validPeriod;
            $updateData['service_charge']= $rateRow->service_charge;
            $updateData['Postage_charge']= $rateRow->Postage_charge;
            $updateData['s_charge_less'] = $rateRow->s_charge_less;
            $updateData['s_charge_greater'] = $rateRow->s_charge_greater;
            $updateData['letter_1_days'] = $rateRow->letter_1_days ?? 21;
            $updateData['letter_2_days'] = $rateRow->letter_2_days ?? 21;
            $updateData['letter_3_days'] = $rateRow->letter_3_days ?? 21;
            $updateData['forfeit_reminder_days'] = $rateRow->forfeit_reminder_days ?? 21;
        }

        TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)
            ->update($updateData);

        if ($hasArrearsLetters && $arrearsPaid + 0.01 >= $requiredArrears) {
            $lifecycle->reactivate(
                TPawnSum::where('id', $pawnSum->id)->firstOrFail(),
                $arrearsPaid,
                'Full arrears paid through repawning; default month-based expiry applied.'
            );
        }

        /* =========================
           PDF GENERATION
        ==========================*/
        $pawnDetails = TPawnDetails::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)
            ->get();

        $karatages = $pawnDetails->pluck('Karatage')->unique()->toArray();

        $pdfData = [
            'repawnSumData'   => TRepawningSum::where('Receipt_Number', $request->receipt_number)
                                    ->where('BC', $branch_code)->get(),
            'pawnSumData'     => TPawnSum::where('Receipt_Number', $request->receipt_number)
                                    ->where('BC', $branch_code)->get(),
            'pawnDetailsData' => $pawnDetails,
            'companyData'     => Company::latest()->first(),
            'branchDetails'   => branchDel::where('bccode', $branch_code)->first(),
            'karatage_data'   => karatage::whereIn('descrption', $karatages)->get(),
            'T_Receipt_Type'  => $resolver->resolveForReceipt(
                TPawnSum::where('Receipt_Number', $request->receipt_number)
                    ->where('BC', $branch_code)->firstOrFail()
            ),
        ];

        $pdf1     = PDF::loadView('reCustomerpawnReceiptPrint', $pdfData);
        $pdfPath1 = storage_path('../public/assets/pdf/Pawn_receipt_customer_' . $branch_code . '.pdf');
        $pdf1->save($pdfPath1);

        $pdf2     = PDF::loadView('two_reOfficepawnReceiptPrint', $pdfData);
        $pdfPath2 = storage_path('../public/assets/pdf/Pawn_receipt_office_' . $branch_code . '.pdf');
        $pdf2->save($pdfPath2);

        DB::commit();

        return back()
            ->with('done',     'Repawning completed successfully')
            ->with('pdfLink1', asset('public/assets/pdf/Pawn_receipt_customer_' . $branch_code . '.pdf'))
            ->with('pdfLink2', asset('public/assets/pdf/Pawn_receipt_office_'   . $branch_code . '.pdf'));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}


    public function show($id)   { }
    public function edit($id)   { }
    public function update(Request $request, $id) { }
    public function destroy($id){ }
}
