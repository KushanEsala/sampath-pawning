<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TPaymentVoucher;
use App\Models\MChartofAccount;
use Illuminate\Support\Facades\DB;
use Datatables;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        if(request()->ajax()) {
            return datatables()->of(TPaymentVoucher::where('BC', auth()->user()->BC)->select('*'))
            ->addColumn('action', 'Action_button')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }

        $ChartAccount = MChartofAccount::all();
        return view('PaymentVoucher')
        -> with("Amount", $ChartAccount);
    }

    public function addPaymentVoucher(Request $request)
    {
        $DepartmentId = $request->id;

        $Department = TPaymentVoucher::updateOrCreate(
            [
                'id' => $DepartmentId
            ],
            [
                'date' => $request->date,
                'cramount' => $request->cramount,
                'crcode' => $request->crcode,
                'dramount' => $request->dramount,
                'drcode' => $request->drcode,
                'description' => $request->description,
                'amount' => $request->amount,
                'OC' => $request->OC,
                'BC' => $request->BC,
            ]
        );

        return Response()->json($Department);
    }

    public function UpdatePaymentVoucher(Request $request)
    {
        $where = array('id' => $request->id);
        $Department = TPaymentVoucher::where($where)->first();

        return Response()->json($Department);
    }

    public function DeletePaymentVoucher(Request $request)
    {
        $Department = TPaymentVoucher::where('id',$request->id)->delete();

        return Response()->json($Department);
    }

    public function GetVoucher(Request $request)
    {
        $Storecode = $request->category;
        $data = MChartofAccount::where('description',$Storecode)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function GetDRVoucher(Request $request)
    {
        $DRcode = $request->amount;
        $data = MChartofAccount::where('description',$DRcode)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function ApprovalPaymentVoucher(Request $request)
    {
        DB::beginTransaction();

        try {
            $receipt = TPaymentVoucher::where('id', $request->id)->first();

            if (!$receipt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ]);
            }

            // Check if already approved
            if ($receipt->status === 'Approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'This record is already approved!'
                ]);
            }

            // Update voucher status
            $receipt->status = 'Approval';
            $receipt->update_date = date('Y-m-d');
            $receipt->save();

            /* =========================
               ACCOUNTING ENTRIES FOR PAYMENT VOUCHER
            ==========================*/
            // Based on your table structure:
            // crcode = Credit Account ID (e.g., '015')
            // drcode = Debit Account ID (e.g., '003')
            // cramount = Credit Account Name (e.g., 'Cash in Hand')
            // dramount = Debit Account Name (e.g., 'Phone Bill')
            // amount = The actual transaction amount (e.g., '100000')

            $voucherNo = 'PV-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT);
            $transDate = $receipt->date ?? date('Y-m-d');
            $branchCode = $receipt->BC ?? auth()->user()->BC;
            $createdBy = auth()->user()->username;

            // Use the account IDs from crcode and drcode (they're already IDs)
            $crAccountId = $receipt->crcode; // This is already an ID like '015'
            $drAccountId = $receipt->drcode; // This is already an ID like '003'

            // Get the account names from cramount and dramount
            $crAccountName = $receipt->cramount; // Account name like 'Cash in Hand'
            $drAccountName = $receipt->dramount; // Account name like 'Phone Bill'

            // Get the actual transaction amount
            $transactionAmount = $receipt->amount;

            // Validate we have all required data
            if (!$crAccountId || !$drAccountId || !$transactionAmount || $transactionAmount <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required data: CR Account ID: ' . $crAccountId . ', DR Account ID: ' . $drAccountId . ', Amount: ' . $transactionAmount
                ]);
            }

            // 1. Debit Entry - Expense/Asset Account
            $debitDescription = ($receipt->description ?? 'Payment voucher') . ' - DR: ' . $drAccountName;
            // 2. Credit Entry - Cash/Bank Account
            $creditDescription = ($receipt->description ?? 'Payment voucher') . ' - CR: ' . $crAccountName;

            DB::table('t_account_trans')->insert([
                'trans_date' => $transDate,
                'voucher_no' => $voucherNo,
                'account_id' => $crAccountId,
                'related_id' => $receipt->id,
                'related_type' => 'PAYMENT_VOUCHER',
                'dr' => 0,
                'cr' => $transactionAmount,
                'description' => $creditDescription,
                'branch_code' => $branchCode,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            /* =========================
               END ACCOUNTING ENTRIES
            ==========================*/

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment voucher approved and posted to accounts successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}