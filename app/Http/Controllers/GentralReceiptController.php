<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TGentralReceipt;
use App\Models\MChartofAccount;
use Illuminate\Support\Facades\DB;
use Datatables;

class GentralReceiptController extends Controller
{
    public function index()
    {
        if(request()->ajax()) {
            return datatables()->of(TGentralReceipt::where('BC', auth()->user()->BC)->select('*'))
            ->addColumn('action', function($row){
                return view('Action_button', [
                    'id' => $row->id,
                    'status' => $row->status ?? ''
                ])->render();
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }

        $ChartAccount = MChartofAccount::all();
        return view('gentralreceipt')
        -> with("Amount", $ChartAccount);
    }

    public function addGentralReceipt(Request $request)
    {
        $DepartmentId = $request->id;

        $Department = TGentralReceipt::updateOrCreate(
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

    public function UpdateGentralReceipt(Request $request)
    {
        $where = array('id' => $request->id);
        $Department = TGentralReceipt::where($where)->first();

        return Response()->json($Department);
    }

    public function DeleteGentralReceipt(Request $request)
    {
        $Department = TGentralReceipt::where('id',$request->id)->delete();

        return Response()->json($Department);
    }

    public function ApprovalGentralReceipt(Request $request)
    {
        DB::beginTransaction();

        try {
            $receipt = TGentralReceipt::where('id', $request->id)->first();

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

            // Update receipt status
            $receipt->status = 'Approval';
            $receipt->update_date = date('Y-m-d');
            $receipt->save();

            /* =========================
               ACCOUNTING ENTRIES FOR GENERAL RECEIPT
               (CASH COMING INTO PAWN OFFICE)
            ==========================*/
            // General Receipt = Money received by pawn office
            // Examples: Interest payments, redemption payments, fees, miscellaneous income
            //
            // Accounting Logic:
            // Debit:  Cash/Bank Account (drcode) - Asset increases, cash comes in
            // Credit: Revenue/Income Account (crcode) - Revenue earned or liability created
            //
            // Table Structure (same as payment voucher):
            // crcode    = Credit Account ID (e.g., '020' for Interest Income)
            // cramount  = Credit Account Name (e.g., 'Interest Income')
            // drcode    = Debit Account ID (e.g., '015' for Cash in Hand)
            // dramount  = Debit Account Name (e.g., 'Cash in Hand')
            // amount    = Transaction amount (e.g., '10000')

            $voucherNo = 'GR-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT);
            $transDate = $receipt->date ?? date('Y-m-d');
            $branchCode = $receipt->BC ?? auth()->user()->BC;
            $createdBy = auth()->user()->username;

            // Use the account IDs from crcode and drcode (already IDs)
            $crAccountId = $receipt->crcode; // Credit Account ID (Income/Revenue)
            $drAccountId = $receipt->drcode; // Debit Account ID (Cash/Bank)

            // Get the account names from cramount and dramount
            $crAccountName = $receipt->cramount; // Credit Account Name
            $drAccountName = $receipt->dramount; // Debit Account Name

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

            // 1. Debit Entry - Cash/Bank Account (Asset increases - money received)
            $debitDescription = ($receipt->description ?? 'General receipt - Cash received') . ' - DR: ' . $drAccountName;

            DB::table('t_account_trans')->insert([
                'trans_date' => $transDate,
                'voucher_no' => $voucherNo,
                'account_id' => $drAccountId,
                'related_id' => $receipt->id,
                'related_type' => 'GENERAL_RECEIPT',
                'dr' => $transactionAmount,
                'cr' => 0,
                'description' => $debitDescription,
                'branch_code' => $branchCode,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Credit Entry - Revenue/Income Account (Revenue earned)
            $creditDescription = ($receipt->description ?? 'General receipt - Income earned') . ' - CR: ' . $crAccountName;
            /* =========================
               END ACCOUNTING ENTRIES
            ==========================*/

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'General receipt approved and posted to accounts successfully'
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