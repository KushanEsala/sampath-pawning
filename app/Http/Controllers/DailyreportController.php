<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TPawnSum;
use App\Models\TRedeemSum;
use App\Models\TOpeningPawnDetails;
use App\Models\TOpeningPawnSum;
use App\Models\TPawnDetails;
use App\Models\TPaymentVoucher;
use App\Models\TGentralReceipt;
use App\Models\DailyReport;
use Carbon\Carbon;
use App\Models\TPawnPayment;
use App\Models\TRepawningSum;

class dailyreportController extends Controller
{
    // Define drcode mappings from m_chartof_accounts
    protected $drcodeMapping = [
        'phone' => '003',
        'electricity' => '001',
        'water' => '007',
        'others' => '103',
        'sundry' => '101',
        'staff_salary' => '002',
        'travelling' => '004',
        'advance' => '006',
        'head_office_return' => '105',
        'western_union_paid' => '102',
    ];

    public function getYesterdayCashBalance()
    {
        $yesterdayDate = Carbon::yesterday();
        $branchCode = auth()->user()->BC;

        $yesterdayReport = DailyReport::where('to_date', $yesterdayDate)
            ->where('BC', $branchCode)
            ->first();

        return $yesterdayReport ? $yesterdayReport->net_balance : 0;
    }

    /**
     * Get payment vouchers filtered by date, branch, and drcode
     */
    protected function getPaymentVouchers($fromDate, $toDate, $branchCode, $drcode)
    {
        return TPaymentVoucher::whereBetween('date', [$fromDate, $toDate])
            ->where('BC', $branchCode)
            ->where('drcode', $drcode)
            ->where('status', 'Approval')
            ->get();
    }

    /**
     * Get all expense categories dynamically
     */
    protected function getAllExpenses($fromDate, $toDate, $branchCode)
    {
        $expenses = [];

        foreach ($this->drcodeMapping as $key => $drcode) {
            $vouchers = $this->getPaymentVouchers($fromDate, $toDate, $branchCode, $drcode);
            $expenses[$key] = [
                'data' => $vouchers,
                'amount' => $vouchers->sum('amount'),
                'formatted' => number_format($vouchers->sum('amount'), 2)
            ];
        }

        return $expenses;
    }

    public function index(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Get pawn transactions
        $quer = TPawnSum::whereBetween('Receipt_Date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
            ->get();

        $TPawnPayment = TPawnPayment::whereBetween('Redeem_Date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
            ->get();

        $query = TRedeemSum::whereBetween('Redeem_Date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
            ->get();

        $TRepawningSum = TRepawningSum::whereBetween('Redeem_Date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
            ->get();

        $Receip = TGentralReceipt::whereBetween('date', [$fromDate, $toDate])
            ->where('BC', $branch_code)
             ->where('status', 'Approval')
            ->get();

        // Get all expenses dynamically
        $expenses = $this->getAllExpenses($fromDate, $toDate, $branch_code);

        // Calculate amounts
        $TPawnPaymentamount = $TPawnPayment->sum('Payable_Total');
        $Pawning = $quer->sum('Amount');
        $Pawningpayemt = number_format($Pawning, 2);

        $int = $query->sum('Original_Pawn_Amount');
        $Redeempayment = number_format($int, 2);

        $TRepawningSumAmount = $TRepawningSum->sum('Payable_Total');

        $Pawn = $query->sum('Paid_Interest');
        $payemt = number_format($Pawn, 2);

        $Stamp = $query->sum('Stamp_Fee');
        $Stamppayemt = number_format($Stamp, 2);

        $Document = $query->sum('Document_Charges');
        $Documen = number_format($Document, 2);

        $headoffice = $Receip->sum('amount');
        $headoffic = number_format($headoffice, 2);

        $pawbalance = $Pawning;
        $pawnbalance = number_format($pawbalance, 2);

        $yesterdayCashBalance = $this->getYesterdayCashBalance();

        // Calculate totals
        $Totalin = $Pawn + $Stamp + $Document + $headoffice + $yesterdayCashBalance + $int +$TPawnPaymentamount;
        $Totalinput = number_format($Totalin, 2);

        // Calculate total expenses dynamically
        $totalExpenses = $Pawning + $TRepawningSumAmount;
        foreach ($expenses as $expense) {
            $totalExpenses += $expense['amount'];
        }

        $totaloutcome = number_format($totalExpenses, 2);
        $netamount = $Totalin - $totalExpenses;
        $payment = number_format($netamount, 2);

        // Save daily report
        if (auth()->check()) {
            DailyReport::updateOrCreate(
                [
                    'to_date' => $toDate,
                    'BC' => auth()->user()->BC,
                ],
                [
                    'net_balance' => $netamount,
                ]
            );
        }

        return view('daily_report')
            ->with("TRepawningSumAmount", $TRepawningSumAmount)
            ->with("summary", $query)
            ->with("quer", $quer)
            ->with("query", $query)
            ->with("Receip", $Receip)
            ->with('Payable_Total', $Redeempayment)
            ->with('Total_Amount', $Pawningpayemt)
            ->with('TPawnPaymentamount', $TPawnPaymentamount)
            ->with('Interest', $payemt)
            ->with('Stamp_Fee', $Stamppayemt)
            ->with('balance', $pawnbalance)
            ->with('tolin', $Totalinput)
            ->with('Document_Charges', $Documen)
            ->with('amountt', $headoffic)
            ->with('totalout', $totaloutcome)
            ->with('expenses', $expenses) // Pass all expenses as array
            ->with('BC', $branch_code)
            ->with('yesterdayCashBalance', $yesterdayCashBalance)
            ->with('net', $payment);
    }
}