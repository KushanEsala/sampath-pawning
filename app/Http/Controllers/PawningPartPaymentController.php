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

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = $resolver->resolveForReceiptCollection($data->first(), $receipt_typ);

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
            ->with('receiptData', $data)
            ->with('financial', $calculator->calculate($data->first()));
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
        $hasArrearsLetters = (bool) ($activeReceipt->is_letter_1 || $activeReceipt->is_letter_2 || $activeReceipt->is_letter_3);
        $financial = $calculator->calculate($activeReceipt);
        // Capture the period before part payment advances the interest start date.
        $interestDays = $financial['days'];
        $request->merge([
            'document_charges' => $financial['service_charge'],
            'Postage_Charges' => $financial['letter_charge'],
        ]);
        $requiredArrears = $hasArrearsLetters ? $financial['arrears_total'] : 0;
        $receivedAmount = (float) ($request->payable_total ?? 0);
        if ($hasArrearsLetters && $receivedAmount + 0.01 < $requiredArrears) {
            throw ValidationException::withMessages([
                'payable_total' => 'Full arrears payment of Rs. '.number_format($requiredArrears, 2).' is required to reactivate this receipt.',
            ]);
        }

        $NewRedeem = new TPawnPayment;
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
            $NewRedeem->Payable_Pawn_Amount = $request->Payable_Pawn_Amount;
            $NewRedeem->Paid_Interest = $request->paid_interest;
            $NewRedeem->Payable_Interest = $request->PayTotalAmount;
            $NewRedeem->Stamp_Fee = $request->stamp_fee;
            $NewRedeem->Document_Charges = $request->document_charges;
            $NewRedeem->Postage_Charges = $request->Postage_Charges;
            $NewRedeem->paid_cap_amount = $request->redeem_total;
            $NewRedeem->Advance_Balance = $request->advance_balance;
            $NewRedeem->Advance_Payment = $request->advance_payment;
            $NewRedeem->Discount = $request->redeem_discount;
            $NewRedeem->Payable_Total = $request->payable_total;
            $NewRedeem->current_pawn_amount = $request->current_pawn_amount;
            $NewRedeem->OC = auth()->user()->username;
            $NewRedeem->BC = auth()->user()->BC;
            $NewRedeem->save();


        $receiptInput_no = $request->receipt_number;
        $Pawn_Receipt_Type = $request->pawn_receipt_type;

        $companyData = Company::latest()->paginate(1);
        $branchData = branchDel::where('bccode', $branch_code)->paginate(1);

        $receipt_advanceAmount = $request->advance_payment;
        $receipt_Interest = $request->paid_interest;
        $Balance = $request->BalanceInterest;
        $pawndate = Carbon::parse($request->redeem_date)->addDay();

        $advancePayments = TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)
            ->get();

        $amounts = $advancePayments->pluck('Pawn_Amount');
        $totalAmount = $amounts->sum();

        $amountsinterest = $advancePayments->pluck('interest_Paid');
        $totalAmountinterest = $amountsinterest->sum();

        $BalanceInterest = $advancePayments->pluck('BalanceInterest');
        $totalBalanceInterest = $BalanceInterest->sum();

        if ($Balance > 0) {
            $totalAdvancePayment = $totalAmount + $Balance ;
        } else {
            $totalAdvancePayment = $totalAmount + $Balance ;
        }
        $totalInterest = $receipt_Interest + $totalAmountinterest;
        $Interest = $Balance + $totalBalanceInterest;

        $validyed_type = $request->validyed_type;
        $final_date_raw = $request->redeem_date;
        $carbon_date = Carbon::parse($final_date_raw);
        $final_date = $carbon_date->addMonths($validyed_type);
        $final_date_string = $final_date->toDateString();

        // Ensure interest balance is not negative
        $interest_Balance = $Balance < 0 ? 0 : $Balance;

        // Create a new Part Payment transaction
        $TPawnTrans = new TPawnTrans;
        $TPawnTrans->Customer_NIC = $request->Customer_NIC;
        $TPawnTrans->Customer_Name = $request->Customer_Name;
        $TPawnTrans->code = $request->receipt_number;
        $TPawnTrans->trans_no = $request->redeem_no;
        $TPawnTrans->trans_type = "PART_PAYMENT";
        $TPawnTrans->trans_amount = $request->redeem_total;
        $TPawnTrans->dDate = $request->redeem_date;
        $TPawnTrans->Cr_amount = 0;
        $TPawnTrans->Dr_amount = $request->payable_total;
        $TPawnTrans->Paided_Interest = $request->paid_interest;
        $TPawnTrans->Pawn_Amount = $request->original_pawn_amount;
        $TPawnTrans->payable_total = $request->payable_total;
        $TPawnTrans->Paided_Captional = $request->advance_payment;
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

        $totalPayment = $request->payable_total;
        $principalPaid = $request->advance_payment ?? 0;
        $interestPaid = $request->paid_interest ?? 0;
        $totalFees = ($request->stamp_fee ?? 0) + ($request->document_charges ?? 0);

        // 1. Debit: Cash Account (Total payment received from customer)
        DB::table('t_account_trans')->insert([
            'trans_date' => $request->redeem_date,
            'voucher_no' => 'PP-' . $request->invoice_number,
            'account_id' => $cashAccountId,
            'related_id' => $request->redeem_no,
            'related_type' => 'PART_PAYMENT',
              'Invoice_no' => $request->invoice_number,
            'dr' => $totalPayment,
            'cr' => 0,
            'description' => 'Part payment received from ' . $request->Customer_Name . ' - Receipt #' .$request->invoice_number,
            'branch_code' => $branch_code,
            'created_by' => auth()->user()->username,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        /* =========================
           END ACCOUNTING ENTRIES
        ==========================*/

        // Fetch rate slabs effective as of the payment date
        $paymentDate = $request->redeem_date ?? now()->toDateString();
        $rates = Recei_Add::active()->effectiveOn($paymentDate)->orderByDesc('pawn_amount')->get();

        // Initialize default values
        $receiptname = null;
        $interest = null;
        $Pawn_Amount_partpayment = $request->original_pawn_amount;
        $payable_total_partpayment = $request->payable_total;
        $totalRepawnPayment = $Pawn_Amount_partpayment - $payable_total_partpayment;

        $isSilver = strtoupper((string) ($activeReceipt->receiptname ?: $activeReceipt->Receipt_Type)) === 'SILVER';

        if ($isSilver) {
            $silverRate = $resolver->resolveByDate('SILVER', $paymentDate);
            $receiptname = 'SILVER';
            $interest = $silverRate->rate1 ?? $activeReceipt->rate1 ?? 0;
        } else {
            foreach ($rates as $rate) {
                if ($totalRepawnPayment >= 100000 && $rate->pawn_amount >= 100000) {
                    $receiptname = $rate->receiptname;
                    $interest = $rate->rate3;
                    break;
                } elseif ($totalRepawnPayment >= 50000 && $totalRepawnPayment <= 99999 && $rate->pawn_amount >= 50000 && $rate->pawn_amount <= 99999) {
                    $receiptname = $rate->receiptname;
                    $interest = $rate->rate3;
                    break;
                } elseif ($totalRepawnPayment < 50000 && $rate->pawn_amount < 50000) {
                    $receiptname = $rate->receiptname;
                    $interest = $rate->rate3;
                    break;
                }
            }
        }

        TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', $branch_code)
            ->update([
                'Pawn_Amount' => $totalAdvancePayment,
                'RePawning_amount' => $totalAdvancePayment,
                'Advance_Payment' => $receipt_advanceAmount,
                'interest_Paid' => $totalInterest,
                'BalanceInterest' => $Interest,
                'Pawn_Date' => $pawndate,
                'Valid_Period' => $validyed_type,
                'Final_date' => $final_date_string,
                'RePawning_date' => $pawndate,
            ]);

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

        $receiptNo = $receipt->Receipt_Number;

        $data = DB::table('t_pawn_trans as trans')
            ->join('t_pawn_sums as sum', 'trans.code', '=', 'sum.Receipt_Number')
            ->where('trans.code', $receiptNo)
            ->where('trans.BC', $branch_code)
            ->where('sum.BC', $branch_code)
            ->select('sum.*', 'trans.*', 'sum.Pawn_Amount as sum_pawn_amount', 'trans.Pawn_Amount as trans_pawn_amount', 'sum.Amount as sum_original_amount')
            ->orderByDesc('trans.dDate')
            ->orderByDesc('trans.id')
            ->get();

        $data = $historyService->appendChargeRows($data, $receipt);
        $data = $historyService->enrichWithRemainingAmounts($data);

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
