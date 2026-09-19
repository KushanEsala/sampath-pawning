<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Models\TPawnTrans;
use App\Models\TPawnDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RedeemLateLettersController extends Controller
{

    // ══════════════════════════════════════════════════════════════
    // INDEX — main Late Letters listing page
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $branch_code     = auth()->user()->BC;
        $currentDateTime = now();
        $receipt_type    = $request->receipt_type;

       $oldPawns = TPawnSum::whereDate('Final_date', '<', $currentDateTime)
    ->where('IsRedeemed', 0)
    ->where('isForfeit', 0)
    ->where('BC', $branch_code)
    ->get();

        $companyData = Company::latest()->paginate(1);
        $receiptType = Recei_Add::all();

        return view('redeem_late_letter')
            ->with('receiptType', $receiptType)
            ->with('companyData', $companyData)
            ->with('recipts', $oldPawns);
    }


    // ══════════════════════════════════════════════════════════════
    // PRINT INDEX — date-filtered print list
    // ══════════════════════════════════════════════════════════════
    public function printIndex(Request $request)
    {
        $branch_code     = auth()->user()->BC;
        $fromDate        = $request->input('from_date');
        $toDate          = $request->input('to_date');
        $currentDateTime = now();

       $oldPawns = TPawnSum::whereDate('Final_date', '<', $currentDateTime)
    ->where('IsRedeemed', 0)
    ->where('BC', $branch_code)
    ->get();

if ($fromDate && $toDate) {
    $oldPawns = TPawnSum::whereBetween('letter_3_date', [$fromDate, $toDate])
        ->orWhereBetween('letter_2_date', [$fromDate, $toDate])
        ->orWhereBetween('letter_1_date', [$fromDate, $toDate])
        ->where('IsRedeemed', 0)
        ->where('BC', $branch_code)
        ->get();
}

        return view('late_redeem_print_list')
            ->with('toDate', $toDate)
            ->with('fromDate', $fromDate)
            ->with('recipts', $oldPawns);
    }


    // ══════════════════════════════════════════════════════════════
    // PRINT (AJAX/JSON) — update letter flag, return JSON
    // ══════════════════════════════════════════════════════════════
    public function print(Request $request)
    {
        $currentDateTime = now();
        $receipt_no      = $request->print_recept_no;
        $letter_no       = $request->letter_no;
        $branch_code     = auth()->user()->BC;

        $T_sumdata    = TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)->get();
        $receiptTypes = $T_sumdata->pluck('Receipt_Type');

        $receiptType = Recei_Add::where('receiptname', $receiptTypes)->get();

        $totalBalanceInterest = $receiptType->pluck('Postage_charge');
        $Postage_charge       = $totalBalanceInterest->sum();

        $TPawnTrans = TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->get();

        $totalPostageCharge   = $TPawnTrans->pluck('Postage_charge');
        $Postage_charge_total = $totalPostageCharge->sum();
        $sumTotalCharge       = $Postage_charge_total + $Postage_charge;

        if ($letter_no == 3) {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_3'      => 1,
                    'letter_pay_three' => $Postage_charge,
                    'letter_3_date'    => $currentDateTime,
                ]);
            TPawnTrans::where('code', $receipt_no)->where('trans_type', 'PAWN')->where('BC', $branch_code)
                ->update(['Postage_charge' => $sumTotalCharge]);
        } elseif ($letter_no == 2) {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_2'    => 1,
                    'letter_pay_two' => $Postage_charge,
                    'letter_2_date'  => $currentDateTime,
                ]);
            TPawnTrans::where('code', $receipt_no)->where('trans_type', 'PAWN')->where('BC', $branch_code)
                ->update(['Postage_charge' => $sumTotalCharge]);
        } else {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_1'    => 1,
                    'letter_pay_one' => $Postage_charge,
                    'letter_1_date'  => $currentDateTime,
                ]);
            TPawnTrans::where('code', $receipt_no)->where('trans_type', 'PAWN')->where('BC', $branch_code)
                ->update(['Postage_charge' => $sumTotalCharge]);
        }

        return response()->json(['status' => 'success']);
    }


    // ══════════════════════════════════════════════════════════════
    // LATE REDEEM — filter by receipt type
    // ══════════════════════════════════════════════════════════════
    public function LateRedeem(Request $request)
    {
        $branch_code     = auth()->user()->BC;
        $currentDateTime = now();
        $receipt_type    = $request->receipt_type;

 $oldPawns = TPawnSum::whereDate('Final_date', '<', $currentDateTime)
    ->where('IsRedeemed', 0)
    ->where('isForfeit', 0)
    ->where('Receipt_Type', $receipt_type)
    ->where('BC', $branch_code)
    ->get();

        $companyData = Company::latest()->paginate(1);
        $receiptType = Recei_Add::all();

        return view('redeem_late_letter', [
            'receiptType' => $receiptType,
            'companyData' => $companyData,
            'recipts'     => $oldPawns,
        ]);
    }


    // ══════════════════════════════════════════════════════════════
    // LATE REDEEM LETTER LIST — today's printed letters summary
    // ══════════════════════════════════════════════════════════════
   public function LateRedeemLetterList(Request $request)
{
    $currentDateTime = now()->toDateString();
    $branch_code     = auth()->user()->BC;
    $letterNumber    = $request->get('letter', null); // 1, 2, or 3

    $query = TPawnSum::where('IsRedeemed', 0)
        ->where('BC', $branch_code)
        ->where('isForfeit', 0);

    // Filter by specific letter or all letters
    if ($letterNumber == 1) {
        $query->whereDate('letter_1_date', $currentDateTime)
              ->where('is_letter_1', 1);
    } elseif ($letterNumber == 2) {
        $query->whereDate('letter_2_date', $currentDateTime)
              ->where('is_letter_2', 1);
    } elseif ($letterNumber == 3) {
        $query->whereDate('letter_3_date', $currentDateTime)
              ->where('is_letter_3', 1);
    } else {
        // Default: all letters (original behavior)
        $query->where(function ($q) use ($currentDateTime) {
            $q->whereDate('letter_1_date', $currentDateTime)
              ->orWhereDate('letter_2_date', $currentDateTime)
              ->orWhereDate('letter_3_date', $currentDateTime);
        })->where(function ($q) {
            $q->where('is_letter_1', 1)
              ->orWhere('is_letter_2', 1)
              ->orWhere('is_letter_3', 1);
        });
    }

    $receiptType = $query->get();
    $companyData = Company::latest()->paginate(1);
    $selectedLetter = $letterNumber;

    return view('lateRedeemLetterList', compact('receiptType', 'companyData', 'selectedLetter'));
}


    // ══════════════════════════════════════════════════════════════
    // PRINT LETTER VIEW — single receipt, opens in new tab
    // Updates letter flag then renders gold_loan_notice_print view
    // ══════════════════════════════════════════════════════════════
    public function printLetterView(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $receipt_no  = $request->receipt_no;
        $letter_no   = (int) $request->letter_no;

        // ── Fetch pawn record ──────────────────────────────────────
        $receipt = TPawnSum::where('Receipt_Number', $receipt_no)
            ->where('BC', $branch_code)
            ->firstOrFail();

        // ── Company info ──────────────────────────────────────────
        $company = Company::latest()->first();

        // ── Interest / postage charge calculation ─────────────────
        $receiptTypeModel = Recei_Add::where('receiptname', $receipt->Receipt_Type)->first();
        $basePostage      = $receiptTypeModel ? (float) $receiptTypeModel->Postage_charge : 0;

        $transPostage = TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->sum('Postage_charge');

        $interest        = $basePostage + $transPostage;
        $currentDateTime = now();

        // ── Update letter flag ────────────────────────────────────
        if ($letter_no === 3) {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_3'      => 1,
                    'letter_pay_three' => $basePostage,
                    'letter_3_date'    => $currentDateTime,
                ]);
        } elseif ($letter_no === 2) {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_2'    => 1,
                    'letter_pay_two' => $basePostage,
                    'letter_2_date'  => $currentDateTime,
                ]);
        } else {
            TPawnSum::where('Receipt_Number', $receipt_no)->where('BC', $branch_code)
                ->update([
                    'is_letter_1'    => 1,
                    'letter_pay_one' => $basePostage,
                    'letter_1_date'  => $currentDateTime,
                ]);
        }

        // Update postage in transactions
        TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->update(['Postage_charge' => $interest]);

        return view('gold_loan_notice_print', compact('receipt', 'company', 'letter_no', 'interest'));
    }


    // ══════════════════════════════════════════════════════════════
    // PRINT BULK LETTERS VIEW — multiple receipts in one tab
    // Opens a single print-ready page with all selected letters,
    // separated by page breaks.
    // Updates the letter flag for every receipt before rendering.
    //
    // GET /print-bulk-letters?receipt_nos[]=X&receipt_nos[]=Y&letter_no=1
    // ══════════════════════════════════════════════════════════════
  public function printBulkLettersViewOld(Request $request)
{
    $branch_code = auth()->user()->BC;
    $letter_no   = (int) $request->letter_no;
    $receiptNos  = (array) $request->input('receipt_nos', []);

    $company = Company::latest()->first();
    $letters = [];

    foreach ($receiptNos as $receipt_no) {

        $receipt = TPawnSum::where('Receipt_Number', $receipt_no)
            ->where('BC', $branch_code)
            ->first();

        if (!$receipt) continue;

        // ── Postage charge calculation ─────────────────────────
        $receiptTypeModel = Recei_Add::where('receiptname', $receipt->Receipt_Type)->first();
        $basePostage      = $receiptTypeModel ? (float) $receiptTypeModel->Postage_charge : 0;

        $transPostage = TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->sum('Postage_charge');

        $interest        = $basePostage + $transPostage;
        $currentDateTime = now();

        // ── Fetch pawn details ─────────────────────────────────
        $pawnDetails = TPawnDetails::where('Receipt_Number', $receipt_no)
            ->where('BC', $branch_code)
            ->get();

        // ── Update letter flag on TPawnSum ─────────────────────
        if ($letter_no === 3) {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_3'      => 1,
                    'letter_pay_three' => $basePostage,
                    'letter_3_date'    => $currentDateTime,
                ]);

        } elseif ($letter_no === 2) {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_2'    => 1,
                    'letter_pay_two' => $basePostage,
                    'letter_2_date'  => $currentDateTime,
                ]);

        } else {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_1'    => 1,
                    'letter_pay_one' => $basePostage,
                    'letter_1_date'  => $currentDateTime,
                ]);
        }

        // ── Update postage in transactions ─────────────────────
        TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->update(['Postage_charge' => $interest]);

        $letters[] = [
            'receipt'     => $receipt,
            'interest'    => $interest,
            'letter_no'   => $letter_no,
            'pawnDetails' => $pawnDetails,
        ];
    }

    return view('gold_loan_notice_bulk_print', compact('letters', 'company', 'letter_no'));
}

public function printBulkLettersView(Request $request)
{
    $branch_code = auth()->user()->BC;
    $letter_no   = (int) $request->letter_no;
    $receiptNos  = (array) $request->input('receipt_nos', []);

    $company = Company::latest()->first();
    $letters = [];

    foreach ($receiptNos as $receipt_no) {

        $receipt = TPawnSum::where('Receipt_Number', $receipt_no)
            ->where('BC', $branch_code)
            ->first();

        if (!$receipt) continue;

        // ── Postage charge calculation ─────────────────────────
        $receiptTypeModel = Recei_Add::where('receiptname', $receipt->Receipt_Type)->first();
        $basePostage      = $receiptTypeModel ? (float) $receiptTypeModel->Postage_charge : 0;

        $transPostage = TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->sum('Postage_charge');

        $interest        = $basePostage + $transPostage;
        $currentDateTime = now();

        // ✅ Fetch ALL pawn details for this receipt
        $pawnDetails = TPawnDetails::where('Receipt_Number', $receipt_no)
            ->get(); // ← removed ->where('BC', $branch_code) if that column doesn't exist in TPawnDetails

        // ── Update letter flag ─────────────────────────────────
        if ($letter_no === 3) {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_3'      => 1,
                    'letter_pay_three' => $basePostage,
                    'letter_3_date'    => $currentDateTime,
                ]);
        } elseif ($letter_no === 2) {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_2'    => 1,
                    'letter_pay_two' => $basePostage,
                    'letter_2_date'  => $currentDateTime,
                ]);
        } else {
            TPawnSum::where('Receipt_Number', $receipt_no)
                ->where('BC', $branch_code)
                ->update([
                    'is_letter_1'    => 1,
                    'letter_pay_one' => $basePostage,
                    'letter_1_date'  => $currentDateTime,
                ]);
        }

        // ── Update postage in transactions ─────────────────────
        TPawnTrans::where('code', $receipt_no)
            ->where('trans_type', 'PAWN')
            ->where('BC', $branch_code)
            ->update(['Postage_charge' => $interest]);

        $letters[] = [
            'receipt'     => $receipt,
            'interest'    => $interest,
            'letter_no'   => $letter_no,
            'pawnDetails' => $pawnDetails,  // ✅ each letter has its own pawnDetails
        ];
    }

    return view('gold_loan_notice_bulk_print', compact('letters', 'company', 'letter_no'));
}

}