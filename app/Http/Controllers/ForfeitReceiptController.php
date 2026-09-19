<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Http\Request;
use App\Models\itemCondition;
use App\Models\karatage;
use App\Models\Item;
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
use App\Models\TForfeitSum;
use App\Models\MPawnfeedback;
use App\Models\TForfitDetails;
use App\Models\TItemMovement;
use App\Models\ForfeitReminderPromise;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptTypeResolver;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ForfeitReceiptController extends Controller
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
    public function index(Request $request)
    {
        return view('ForfeitReceipt', ['prefillReceiptNumber' => $request->query('receipt_number')]);
    }

public function store(Request $request, ReceiptLifecycleService $lifecycle)
{
    // ✅ Step 1: Validate input
    $request->validate([
        'receipt_number'       => 'required',
        'invoice_number'       => 'required',
        'pawn_receipt_type'    => 'required',
        'total_weight'         => 'required|numeric',
        'pawn_weight'          => 'required|numeric',
        'forfeit_date'         => 'required|date',
        'forfeit_no'           => 'required',
        'original_pawn_amount' => 'required|numeric',
        'stamp_fee'            => 'nullable|numeric',
        'document_charges'     => 'nullable|numeric',
        'advance_balance'      => 'nullable|numeric',
        'redeem_discount'      => 'nullable|numeric',
    ]);

    DB::beginTransaction();

    try {
        $candidateReceipt = TPawnSum::where('Receipt_Number', $request->receipt_number)
            ->where('BC', auth()->user()->BC)->where('IsRedeemed', 0)->where('isForfeit', 0)
            ->lockForUpdate()->firstOrFail();
        if (TForfeitSum::where('Receipt_Number', $request->receipt_number)->where('BC', auth()->user()->BC)->exists()) {
            throw new \RuntimeException('This receipt has already been forfeited.');
        }
        if (!$candidateReceipt->forfeit_queued_at) {
            throw new \RuntimeException('Review and move this receipt from Forfeit Reminder List before final forfeiture.');
        }

        // ✅ Step 2: Save to TForfeitSum
        $NewForfeit = new TForfeitSum;
        $NewForfeit->Receipt_Number       = $request->receipt_number;
        $NewForfeit->Invoice_Number       = $request->invoice_number;
        $NewForfeit->Pawn_Receipt_Type    = $request->pawn_receipt_type;
        $NewForfeit->Total_Weight         = $request->total_weight;
        $NewForfeit->Pawn_Weight          = $request->pawn_weight;
        $NewForfeit->Forfeit_Date         = $request->forfeit_date;
        $NewForfeit->Forfeit_Number       = $request->forfeit_no;
        $NewForfeit->Original_Pawn_Amount = $request->original_pawn_amount;
        $calcInterest = null;
        if (!$request->filled('payable_interest') && !$request->filled('paid_interest')) {
            $calcDate = $request->forfeit_date ?: now();
            $financial = app(\App\Services\ReceiptFinancialCalculator::class)->calculate($candidateReceipt, $calcDate);
            $calcInterest = $financial['interest'];
        }
        $NewForfeit->Paid_Interest        = $request->paid_interest ?: ($request->payable_interest ?: $calcInterest);
        $NewForfeit->Payable_Interest     = $request->payable_interest ?: ($request->paid_interest ?: $calcInterest);
        $NewForfeit->Stamp_Fee            = $request->stamp_fee;
        $NewForfeit->Document_Charges     = $request->document_charges;
        $NewForfeit->Advance_Balance      = $request->advance_balance;
        $NewForfeit->Discount             = $request->redeem_discount;
        $NewForfeit->Payable_Total        = $request->payable_total;
        $NewForfeit->OC                   = auth()->user()->username;
        $NewForfeit->BC                   = auth()->user()->BC;
        $NewForfeit->save();

        $receiptInput_no = $request->receipt_number;
        $branch_code     = auth()->user()->BC;

        // ✅ Step 3: Update isForfeit in TPawnSum & TPawnDetails
        TPawnSum::where('Receipt_Number', $receiptInput_no)
            ->where('BC', $branch_code)
            ->update(['isForfeit' => 1]);

        \App\Services\ReceiptArticleStatus::sync($candidateReceipt->fresh());

        // ✅ Step 4: Fetch related data
        $T_sumdata     = $candidateReceipt->fresh();
        $lifecycle->record($T_sumdata, 'FORFEITED', (float) $request->payable_total, 'Receipt moved from Forfeit Reminder to final forfeiture.');
        if (Schema::hasTable('forfeit_reminder_promises')) {
            ForfeitReminderPromise::where('pawn_sum_id', $T_sumdata->id)
                ->whereIn('status', ['PENDING', 'EXTENDED'])
                ->update(['status' => 'BROKEN', 'updated_by' => auth()->user()->username, 'updated_at' => now()]);
        }
        $T_detailsdata = TPawnDetails::where('Receipt_Number', $receiptInput_no)->where('BC', $branch_code)->get();
        $companyData   = Company::latest()->first();

        // ✅ Step 5: Save to TForfeitDetails and Item
        $latestCode = Item::max('Item_code');
        $nextCode   = $latestCode ? intval($latestCode) + 1 : 10000;

        foreach ($T_detailsdata as $detail) {
            // Save to TForfeitDetails
            $forfeitDetail = new TForfitDetails();
            $forfeitDetail->Receipt_Number = $detail->Receipt_Number;
            $forfeitDetail->Receipt_Type   = $request->pawn_receipt_type;
            $forfeitDetail->Category       = $detail->Category ?? null;
            $forfeitDetail->Articles       = $detail->Articles ?? null;
            $forfeitDetail->Condition      = $detail->Condition ?? null;
            $forfeitDetail->Karatage       = $detail->Karatage ?? null;
            $forfeitDetail->Weight         = $detail->Weight;
            $forfeitDetail->QTY            = $detail->QTY;
            $forfeitDetail->Value          = $detail->Value;
            $forfeitDetail->Date           = $request->forfeit_date;
            $forfeitDetail->IsRedeemed     = 0;
            $forfeitDetail->isForfeit      = 1;
            $forfeitDetail->Total_Weight   = $detail->Total_Weight ?? $detail->Weight;
            $forfeitDetail->OC             = auth()->user()->username;
            $forfeitDetail->BC             = auth()->user()->BC;
            $forfeitDetail->save();

            // ✅ Market rate calculation (assuming 8 = palan conversion, clarify if not)
            $marketRate = karatage::where('descrption', $detail->Karatage)->value('salepriceRate');
            $marketRateOneGram = $marketRate ? $marketRate / 8 : 0;
            $weight = $detail->Total_Weight ?? $detail->Weight;
            $marketRateprice = $marketRateOneGram * $weight;

            // ✅ Save to Item
            $Item_code = str_pad($nextCode, 5, '0', STR_PAD_LEFT);
            Item::create([
                'category'         => $detail->Category,
                'Receipt_Number'   => $detail->Receipt_Number,
                'Item_code'        => $Item_code,
                'Bar_code'         => $Item_code . '-' . \Carbon\Carbon::parse($request->forfeit_date)->format('Ymd') . '-' . $detail->Receipt_Number,
                'Item_description' => $detail->Articles,
                'Brand'            => $detail->Condition,
                'Color'            => null,
                'Make'             => $detail->Karatage,
                'image'            => null,
                'purchasePrice'    => $marketRate,
                'saleprice'        => $marketRateprice,
                'Total_Weight'     => $detail->Total_Weight ?? $detail->Weight,
                'Weight'           => $detail->Weight,
                'QTY'              => $detail->QTY,
                'Branch'           => auth()->user()->Branch,
                'BC'               => auth()->user()->BC,
            ]);

            // ✅ Item Movement
            $ItemMovementDetails = new TItemMovement;
            $ItemMovementDetails->trans_no   = $request->receipt_number;
            $ItemMovementDetails->dDate      = $request->forfeit_date;
            $ItemMovementDetails->Category   = $detail->Category;
            $ItemMovementDetails->trans_code = "FORFEIT";
            $ItemMovementDetails->item_code  = $Item_code;
            $ItemMovementDetails->qun_in     = $detail->QTY;
            $ItemMovementDetails->bc         = auth()->user()->BC;
            $ItemMovementDetails->save();

            $nextCode++;
        }

        // ✅ Step 6: Generate PDF
        $pdfPath = public_path('assets/pdf/RepawnReceiptPrint' . $branch_code . '.pdf');
        $pdf = PDF::loadView('ForfeitReceiptPrint', [
            'pawnSumData'     => $T_sumdata,
            'pawnDetailsData' => $T_detailsdata,
            'companyData'     => $companyData
        ]);
        $pdf->save($pdfPath);

           $pdfUrl = asset('public/assets/pdf/RepawnReceiptPrint'.$branch_code.'.pdf');

        DB::commit();

        return back()
            ->with('done', 'The Forfeit has been added successfully.')
            ->with('pdfLink', $pdfUrl);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to store forfeit', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);
        return back()->withErrors(['error' => 'Failed to save forfeit: ' . $e->getMessage()]);
    }
}




    public function create()
    {
       
    }


   //receipt Search function
   public function search(Request $request, ?ReceiptTypeResolver $resolver = null)
   {
       $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
       $receiptNo = $request->search_receipt_no;
       $branch_code = auth()->user()->BC;

       $dataTPawnSum = TPawnSum::where('Receipt_Number',$receiptNo)
                       ->where('IsRedeemed', 0)
                       ->where('isForfeit', 0)
                       ->where('BC',$branch_code)
                       ->get();




        $data = $dataTPawnSum;

       if($data->count()>0){

           $cus_nic = $data->first()->Customer_NIC;
           $cus_data = Customer::where('NIC', $cus_nic)->get();

           $receipt_typ = $data->first()->Receipt_Type;
           $receipt_data = $resolver->resolveForReceiptCollection($data->first(), $receipt_typ);

           $maxRedeemNo = TForfeitSum::where('BC',$branch_code)
                            ->orderBy('Forfeit_Number', 'desc')
                            ->value('Forfeit_Number');

           $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

           $pawn_type = "Pawn";


        $MPawnfeedback = MPawnfeedback::where('Receipt_Number',$receiptNo)
                       ->where('BC',$branch_code)
                       ->get();


           return view('forfeitSearch')
           ->with("maxRedeem", $maxRedeemNos)
           ->with('customerData', $cus_data)
           ->with('receiptTypeData', $receipt_data)
           ->with('MPawnfeedback', $MPawnfeedback)
           ->with('pawnType', $pawn_type)
           ->with('receiptData', $data);
       }else{
           return response()->json([
               'status'=>'not_found'
           ]);
       }
   }

    //invoice Search function
    public function searchInvoice(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $invoiceNo = $request->search_invoice_no;
        $branch_code = auth()->user()->BC;

        $TOpeningPawnSumdata = TOpeningPawnSum::where('Invoice_Number',$invoiceNo)
                                ->where('IsRedeemed', 0)
                                ->where('isForfeit', 0)
                                ->where('BC',$branch_code)
                                ->get();

        $data = $TOpeningPawnSumdata;

        if($data->count()>0){

            $cus_nic = $data->first()->Customer_NIC;
            $cus_data = Customer::where('NIC', $cus_nic)->get();

            $receipt_typ = $data->first()->Receipt_Type;
            $receipt_data = $resolver->resolveForReceiptCollection($data->first(), $receipt_typ);

            $maxRedeemNo = TForfeitSum::where('BC',$branch_code)
                                    ->orderBy('Forfeit_Number', 'desc')
                                    ->value('Forfeit_Number');

            $maxRedeemNos = str_pad($maxRedeemNo, 4, '0', STR_PAD_LEFT);

            $pawn_type = "Opening_Pawn";

            return view('forfeitSearch')
            ->with("maxRedeem", $maxRedeemNos)
            ->with('customerData', $cus_data)
            ->with('receiptTypeData', $receipt_data)
            ->with('pawnType', $pawn_type)
            ->with('receiptData', $data);
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

public function ForfeitReceiptList(Request $request, ReceiptHistoryService $history)
{
    $request->validate(['receipt_number' => 'nullable|string|max:80', 'status' => 'nullable|in:all,pending,forfeited', 'per_page' => 'nullable|in:10,25,50,100']);
    $status = $request->input('status', 'all');
    $perPage = (int) $request->input('per_page', 25);
    $hasQueue = Schema::hasColumn('t_pawn_sums', 'forfeit_queued_at');
    $query = TPawnSum::where('BC', auth()->user()->BC)
        ->where(function ($query) use ($hasQueue) {
            $query->where('isForfeit', 1);
            if ($hasQueue) {
                $query->orWhere(fn ($pending) => $pending->where('IsRedeemed', 0)->where('isForfeit', 0)->whereNotNull('forfeit_queued_at'));
            }
        });
    if ($request->filled('receipt_number')) $query->where('Receipt_Number', trim((string) $request->input('receipt_number')));
    if ($status === 'pending') {
        $query->where('isForfeit', 0);
        $hasQueue ? $query->whereNotNull('forfeit_queued_at') : $query->whereRaw('1 = 0');
    }
    if ($status === 'forfeited') $query->where('isForfeit', 1);
    $recipts = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    $recipts->getCollection()->each(function (TPawnSum $receipt) use ($history) {
        $receipt->setAttribute('receipt_history', $history->build($receipt));
        $receipt->setAttribute('forfeit_record', TForfeitSum::where('BC', $receipt->BC)
            ->where('Receipt_Number', $receipt->Receipt_Number)->orderByDesc('Forfeit_Number')->first());
        $receipt->setAttribute('stock_items', Item::where('BC', $receipt->BC)
            ->where('Receipt_Number', $receipt->Receipt_Number)->get());
    });
    return view('forfeitReceipt_List', compact('recipts', 'status'));
}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function getReceiptDetails(Request $request)
        {
            $receiptNumber = $request->receipt_number;

            $feedbacks = MPawnfeedback::where('Receipt_Number', $receiptNumber)->get();

            if ($feedbacks->isNotEmpty()) {
                return view('partials.receipt-details', compact('feedbacks'));
            } else {
                return "<p>No receipt feedback found.</p>";
            }
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
