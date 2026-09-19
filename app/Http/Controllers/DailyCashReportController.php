<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TDailyBalance;

class DailyCashReportController extends Controller
{
    public function index(Request $request)
    {
        $branchCode = auth()->user()->BC;

        // Get date range from request or default to current month
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        return view('daily_cash_report', compact('dateFrom', 'dateTo'));
    }

    public function generate(Request $request)
    {
        $branchCode = auth()->user()->BC;
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Get beginning balance from saved daily balances or calculate
        $beginningBalance = $this->getBeginningBalance($branchCode, $dateFrom);

        // Get all transactions for the period
        $transactions = DB::table('t_account_trans')
            ->whereBetween('trans_date', [$dateFrom, $dateTo])
            ->where('branch_code', $branchCode)
            ->orderBy('trans_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Group transactions by date and calculate running balance
        $reportData = $this->processTransactions($transactions, $beginningBalance);

        // Calculate period totals
        $periodTotals = $this->calculatePeriodTotals($reportData);

        // Calculate ending balance
        $endingBalance = $beginningBalance + ($periodTotals['raw_total_dr'] - $periodTotals['raw_total_cr']);

        // Save ending balance for next period
        $this->saveDailyBalance($branchCode, $dateTo, $endingBalance);

        return view('daily_cash_report_view', compact(
            'reportData',
            'beginningBalance',
            'periodTotals',
            'dateFrom',
            'dateTo',
            'branchCode',
            'endingBalance'
        ));
    }

    /**
     * Get beginning balance for a specific date
     * First checks t_daily_balances table for previous day's ending balance
     * If not found, calculates from all transactions before the date
     */
    private function getBeginningBalance($branchCode, $date)
    {
        // Try to get the ending balance from the previous day
        $previousDate = Carbon::parse($date)->subDay()->format('Y-m-d');

        $savedBalance = DB::table('t_daily_balances')
            ->where('branch_code', $branchCode)
            ->where('balance_date', $previousDate)
            ->value('ending_balance');

        if ($savedBalance !== null) {
            return $savedBalance;
        }

        // If no saved balance, calculate from all transactions before this date
        $calculatedBalance = DB::table('t_account_trans')
            ->where('branch_code', $branchCode)
            ->where('trans_date', '<', $date)
            ->selectRaw('SUM(dr) - SUM(cr) as balance')
            ->value('balance');

        return $calculatedBalance ?? 0;
    }

    /**
     * Save or update daily balance
     */
    private function saveDailyBalance($branchCode, $date, $endingBalance)
    {
        // Calculate total debits and credits for the day
        $dayTotals = DB::table('t_account_trans')
            ->where('branch_code', $branchCode)
            ->where('trans_date', $date)
            ->selectRaw('SUM(dr) as total_dr, SUM(cr) as total_cr')
            ->first();

        // Get beginning balance for this day
        $beginningBalance = $this->getBeginningBalance($branchCode, $date);

        // Insert or update the daily balance record
        DB::table('t_daily_balances')->updateOrInsert(
            [
                'branch_code' => $branchCode,
                'balance_date' => $date
            ],
            [
                'beginning_balance' => $beginningBalance,
                'total_debit' => $dayTotals->total_dr ?? 0,
                'total_credit' => $dayTotals->total_cr ?? 0,
                'ending_balance' => $endingBalance,
                'created_by' => auth()->user()->username,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())')
            ]
        );
    }

    /**
     * Process daily closing - save ending balance for the day
     */
    public function closeDailyBalance(Request $request)
    {
        DB::beginTransaction();

        try {
            $branchCode = auth()->user()->BC;
            $date = $request->input('date', date('Y-m-d'));

            // Get beginning balance
            $beginningBalance = $this->getBeginningBalance($branchCode, $date);

            // Calculate day's transactions
            $dayTotals = DB::table('t_account_trans')
                ->where('branch_code', $branchCode)
                ->where('trans_date', $date)
                ->selectRaw('SUM(dr) as total_dr, SUM(cr) as total_cr')
                ->first();

            $totalDr = $dayTotals->total_dr ?? 0;
            $totalCr = $dayTotals->total_cr ?? 0;

            // Calculate ending balance
            $endingBalance = $beginningBalance + ($totalDr - $totalCr);

            // Save the balance
            $this->saveDailyBalance($branchCode, $date, $endingBalance);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily balance closed successfully',
                'data' => [
                    'date' => $date,
                    'beginning_balance' => number_format($beginningBalance, 2),
                    'total_debit' => number_format($totalDr, 2),
                    'total_credit' => number_format($totalCr, 2),
                    'ending_balance' => number_format($endingBalance, 2)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error closing daily balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily balance for a specific date
     */
    public function getDailyBalance(Request $request)
    {
        $branchCode = auth()->user()->BC;
        $date = $request->input('date', date('Y-m-d'));

        $balance = DB::table('t_daily_balances')
            ->where('branch_code', $branchCode)
            ->where('balance_date', $date)
            ->first();

        if ($balance) {
            return response()->json([
                'success' => true,
                'data' => $balance
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No balance record found for this date'
        ], 404);
    }

    /**
     * Recalculate all daily balances for a date range
     * Useful for fixing any discrepancies
     */
    public function recalculateBalances(Request $request)
    {
        DB::beginTransaction();

        try {
            $branchCode = auth()->user()->BC;
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Get starting balance (day before dateFrom)
            $startDate = Carbon::parse($dateFrom);
            $endDate = Carbon::parse($dateTo);

            $currentBalance = $this->getBeginningBalance($branchCode, $dateFrom);

            // Loop through each day and recalculate
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateString = $current->format('Y-m-d');

                // Get day's transactions
                $dayTotals = DB::table('t_account_trans')
                    ->where('branch_code', $branchCode)
                    ->where('trans_date', $dateString)
                    ->selectRaw('SUM(dr) as total_dr, SUM(cr) as total_cr')
                    ->first();

                $totalDr = $dayTotals->total_dr ?? 0;
                $totalCr = $dayTotals->total_cr ?? 0;

                $beginningBalance = $currentBalance;
                $endingBalance = $beginningBalance + ($totalDr - $totalCr);

                // Save balance
                DB::table('t_daily_balances')->updateOrInsert(
                    [
                        'branch_code' => $branchCode,
                        'balance_date' => $dateString
                    ],
                    [
                        'beginning_balance' => $beginningBalance,
                        'total_debit' => $totalDr,
                        'total_credit' => $totalCr,
                        'ending_balance' => $endingBalance,
                        'created_by' => auth()->user()->username,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );

                $currentBalance = $endingBalance;
                $current->addDay();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Balances recalculated successfully from ' . $dateFrom . ' to ' . $dateTo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error recalculating balances: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processTransactions($transactions, $beginningBalance)
    {
        $reportData = [];
        $runningBalance = $beginningBalance;

        foreach ($transactions as $transaction) {
            // Calculate net amount (Dr - Cr)
            $netAmount = $transaction->dr - $transaction->cr;
            $runningBalance += $netAmount;

            // Determine transaction description based on type
            $description = $this->formatDescription($transaction);

            $reportData[] = [
                'date' => $transaction->trans_date,
                'no' => $transaction->voucher_no,
                'Invoice_no' => $transaction->Invoice_no,
                'transaction' => $transaction->related_type,
                  'created_at' => $transaction->created_at,  // ← add this
                'description' => $description,
                'dr_amount' => $transaction->dr > 0 ? number_format($transaction->dr, 2) : '-',
                'cr_amount' => $transaction->cr > 0 ? number_format($transaction->cr, 2) : '-',
                'balance' => number_format($runningBalance, 2),
                'raw_dr' => $transaction->dr,
                'raw_cr' => $transaction->cr,
            ];
        }

        return $reportData;
    }

    private function formatDescription($transaction)
    {
        $baseDescription = $transaction->description ?? '';

        switch ($transaction->related_type) {
            case 'PAWN':
                if (strpos($baseDescription, 'issued to') !== false) {
                    return 'Pawning - Loan Issued';
                } else {
                    return 'Pawning - Cash Disbursed';
                }
                break;

            case 'REDEEM':
                if (strpos($baseDescription, 'Cash received') !== false) {
                    return 'Redeem - Payment Received';
                } else if (strpos($baseDescription, 'loan settled') !== false) {
                    return 'Redeem - Loan Settled';
                } else if (strpos($baseDescription, 'Interest income') !== false) {
                    return 'Redeem - Interest Income';
                } else {
                    return 'Redeem - Fees';
                }
                break;

            case 'REPAWNING':
                if (strpos($baseDescription, 'increased loan') !== false) {
                    return 'Repawning - New Loan';
                } else if (strpos($baseDescription, 'close original') !== false) {
                    return 'Repawning - Close Old Loan';
                } else if (strpos($baseDescription, 'Extra cash') !== false) {
                    return 'Repawning - Cash Disbursed';
                } else if (strpos($baseDescription, 'Interest payment received') !== false) {
                    return 'Repawning - Interest Received';
                } else {
                    return 'Repawning - Interest Income';
                }
                break;

            case 'PART_PAYMENT':
                if (strpos($baseDescription, 'received from') !== false) {
                    return 'Part Payment - Cash Received';
                } else if (strpos($baseDescription, 'Principal') !== false) {
                    return 'Part Payment - Principal Reduction';
                } else if (strpos($baseDescription, 'Interest') !== false) {
                    return 'Part Payment - Interest Income';
                } else {
                    return 'Part Payment - Fees';
                }
                break;

            case 'PAYMENT_VOUCHER':
                return 'Payment Voucher - ' . $this->extractAccountName($baseDescription);
                break;

            case 'GENERAL_RECEIPT':
                return 'General Receipt - ' . $this->extractAccountName($baseDescription);
                break;

            default:
                return $baseDescription;
        }
    }

    private function extractAccountName($description)
    {
        if (preg_match('/(DR|CR):\s*([^,]+)/', $description, $matches)) {
            return trim($matches[2]);
        }
        return 'Transaction';
    }

    private function calculatePeriodTotals($reportData)
    {
        $totalDr = 0;
        $totalCr = 0;

        foreach ($reportData as $row) {
            $totalDr += $row['raw_dr'];
            $totalCr += $row['raw_cr'];
        }

        $endingBalance = end($reportData)['balance'] ?? 0;

        return [
            'total_dr' => number_format($totalDr, 2),
            'total_cr' => number_format($totalCr, 2),
            'ending_balance' => $endingBalance,
            'raw_total_dr' => $totalDr,
            'raw_total_cr' => $totalCr,
        ];
    }
}