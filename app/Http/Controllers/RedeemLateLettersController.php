<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Models\TPawnTrans;
use App\Models\TPawnDetails;
use App\Models\branchDel;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ArrearsLetterEvent;
use App\Services\ArrearsLetterService;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptTypeResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RedeemLateLettersController extends Controller
{

    // ══════════════════════════════════════════════════════════════
    // INDEX — main Late Letters listing page
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request, ?ReceiptFinancialCalculator $calculator = null, ?ReceiptTypeResolver $resolver = null)
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        return $this->buildView($request, $calculator, $resolver);
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
    public function print(Request $request, ArrearsLetterService $letterService)
    {
        $receipt = TPawnSum::where('Receipt_Number', $request->print_recept_no)
            ->where('BC', auth()->user()->BC)->firstOrFail();
        $event = $letterService->issue($receipt->id, auth()->user()->BC, (int) $request->letter_no);

        return response()->json(['status' => 'success', 'event_id' => $event->id]);
    }


    // ══════════════════════════════════════════════════════════════
    // LATE REDEEM — filter by receipt type
    // ══════════════════════════════════════════════════════════════
    public function LateRedeem(Request $request, ?ReceiptFinancialCalculator $calculator = null, ?ReceiptTypeResolver $resolver = null)
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        return $this->buildView($request, $calculator, $resolver);
    }

    /**
     * Shared view builder for index + LateRedeem — handles per-tab pagination.
     */
    private function buildView(Request $request, ReceiptFinancialCalculator $calculator, ReceiptTypeResolver $resolver)
    {
        $branch_code = auth()->user()->BC;
        $activeTab   = (int) $request->input('tab', 1);

        // Load only the active tab's paginated data
        $tab1 = $activeTab === 1 ? $this->eligibleReceipts($request, $branch_code, $calculator, 1) : null;
        $tab2 = $activeTab === 2 ? $this->eligibleReceipts($request, $branch_code, $calculator, 2) : null;
        $tab3 = $activeTab === 3 ? $this->eligibleReceipts($request, $branch_code, $calculator, 3) : null;

        // Total counts per tab (cheap COUNT queries, no data hydration)
        $schedule = new \App\Services\ReceiptPenaltySchedule();
        $today    = today()->toDateString();
        $baseCount = TPawnSum::where('BC', $branch_code)->where('IsRedeemed', 0)->where('isForfeit', 0);
        if ($request->filled('receipt_type')) { $baseCount->where('Receipt_Type', $request->receipt_type); }
        if ($request->filled('receipt_number')) { $baseCount->where('Receipt_Number', $request->receipt_number); }
        $count_1st = (clone $baseCount)
            ->whereRaw($schedule->letterDueSql(1).' <= ?', [$today])
            ->where(fn ($f) => $f->whereNull('is_letter_1')->orWhere('is_letter_1', 0))->count();
        $count_2nd = (clone $baseCount)->where('is_letter_1', 1)
            ->where(fn ($f) => $f->whereNull('is_letter_2')->orWhere('is_letter_2', 0))
            ->whereRaw($schedule->letterDueSql(2).' <= ?', [$today])->count();
        $count_3rd = (clone $baseCount)->where('is_letter_2', 1)
            ->where(fn ($f) => $f->whereNull('is_letter_3')->orWhere('is_letter_3', 0))
            ->whereRaw($schedule->letterDueSql(3).' <= ?', [$today])->count();

        $companyData = Company::latest()->paginate(1);
        $receiptType = $resolver->getActiveTypes();

        return view('redeem_late_letter', compact(
            'receiptType', 'companyData',
            'tab1', 'tab2', 'tab3',
            'count_1st', 'count_2nd', 'count_3rd',
            'activeTab'
        ));
    }


    // ══════════════════════════════════════════════════════════════
    // LATE REDEEM LETTER LIST — today's printed letters summary
    // ══════════════════════════════════════════════════════════════
public function LateRedeemLetterList(Request $request)
{
    $branch_code  = auth()->user()->BC;
    $letterNumber = $request->get('letter', null);

    // Date range — default to today if not provided
    $fromDate = $request->get('from_date', now()->toDateString());
    $toDate   = $request->get('to_date', now()->toDateString());

    $query = TPawnSum::where('IsRedeemed', 0)
        ->where('BC', $branch_code)
        ->where('isForfeit', 0);

    if ($letterNumber == 1) {
        $query->whereBetween('letter_1_date', [$fromDate, $toDate])
              ->where('is_letter_1', 1);
    } elseif ($letterNumber == 2) {
        $query->whereBetween('letter_2_date', [$fromDate, $toDate])
              ->where('is_letter_2', 1);
    } elseif ($letterNumber == 3) {
        $query->whereBetween('letter_3_date', [$fromDate, $toDate])
              ->where('is_letter_3', 1);
    } else {
        $query->where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('letter_1_date', [$fromDate, $toDate])
              ->orWhereBetween('letter_2_date', [$fromDate, $toDate])
              ->orWhereBetween('letter_3_date', [$fromDate, $toDate]);
        })->where(function ($q) {
            $q->where('is_letter_1', 1)
              ->orWhere('is_letter_2', 1)
              ->orWhere('is_letter_3', 1);
        });
    }

    $receiptType    = $query->get();
    $companyData    = Company::latest()->paginate(1);
    $selectedLetter = $letterNumber;
    $branchData     = branchDel::where('bccode', $branch_code)->first(); // returns single model or null

    return view('lateRedeemLetterList', compact(
        'receiptType', 'companyData', 'selectedLetter', 'fromDate', 'toDate', 'branchData'
    ));
}


    // ══════════════════════════════════════════════════════════════
    // PRINT LETTER VIEW — single receipt, opens in new tab
    // Updates letter flag then renders gold_loan_notice_print view
    // ══════════════════════════════════════════════════════════════
    public function issue(Request $request, ArrearsLetterService $letterService)
    {
        $validated = $request->validate([
            'pawn_sum_id' => ['required', 'integer'],
            'letter_no' => ['required', Rule::in([1, 2, 3])],
        ]);

        $event = $letterService->issue((int) $validated['pawn_sum_id'], auth()->user()->BC, (int) $validated['letter_no']);

        return response()->json([
            'status' => 'success',
            'print_url' => route('print_letter_view', ['event_id' => $event->id]),
        ]);
    }

    public function issueBulk(Request $request, ArrearsLetterService $letterService)
    {
        $validated = $request->validate([
            'pawn_sum_ids' => ['required', 'array', 'min:1'],
            'pawn_sum_ids.*' => ['integer'],
            'letter_no' => ['required', Rule::in([1, 2, 3])],
        ]);
        $eventIds = DB::transaction(function () use ($validated, $letterService) {
            $ids = [];
            foreach (array_unique($validated['pawn_sum_ids']) as $pawnSumId) {
                $ids[] = $letterService->issue((int) $pawnSumId, auth()->user()->BC, (int) $validated['letter_no'])->id;
            }
            return $ids;
        });

        return response()->json([
            'status' => 'success',
            'print_url' => route('print_bulk_letters_view', ['event_ids' => $eventIds]),
        ]);
    }

    public function printLetterView(Request $request, ?ReceiptFinancialCalculator $calculator = null, ?ReceiptTypeResolver $resolver = null)
    {
        $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
        $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
        $branch_code = auth()->user()->BC;
        $event = ArrearsLetterEvent::where('id', $request->event_id)->where('BC', $branch_code)->firstOrFail();
        $receipt = TPawnSum::where('id', $event->pawn_sum_id)->where('BC', $branch_code)->firstOrFail();
        $this->applyCurrentCustomerContact($receipt);
        $letter_no = (int) $event->letter_no;
        $company = Company::latest()->first();
        $receiptTypeModel = $resolver->resolveForReceipt($receipt);
        $financial = $calculator->calculate($receipt);
        $letters = [[
            'receipt' => $receipt,
            'interest' => $financial['interest'],
            'letter_no' => $letter_no,
            'pawnDetails' => TPawnDetails::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $branch_code)->get(),
            'receiptType' => $receiptTypeModel,
            'branchDetails' => branchDel::where('bccode', $branch_code)->first(),
            'financial' => $financial,
        ]];

        return view('gold_loan_notice_bulk_print', compact('letters', 'company', 'letter_no'));
    }


    // ══════════════════════════════════════════════════════════════
    // PRINT BULK LETTERS VIEW — multiple receipts in one tab
    // Opens a single print-ready page with all selected letters,
    // separated by page breaks.
    // Updates the letter flag for every receipt before rendering.
    //
    // GET /print-bulk-letters?receipt_nos[]=X&receipt_nos[]=Y&letter_no=1
    // ══════════════════════════════════════════════════════════════
public function printBulkLettersView(Request $request, ?ReceiptFinancialCalculator $calculator = null, ?ReceiptTypeResolver $resolver = null)
{
    $calculator  = $calculator ?? app(ReceiptFinancialCalculator::class);
    $resolver    = $resolver ?? app(ReceiptTypeResolver::class);
    $branch_code = auth()->user()->BC;
    $company = Company::latest()->first();
    $letters = [];
    $events = ArrearsLetterEvent::whereIn('id', (array) $request->input('event_ids', []))
        ->where('BC', $branch_code)->orderBy('id')->get();
    $letter_no = (int) optional($events->first())->letter_no;

    foreach ($events as $event) {
        $receipt = TPawnSum::where('id', $event->pawn_sum_id)->where('BC', $branch_code)->first();
        if (!$receipt) {
            continue;
        }
        $this->applyCurrentCustomerContact($receipt);
        $receiptTypeModel = $resolver->resolveForReceipt($receipt);
        $financial = $calculator->calculate($receipt);
        $letters[] = [
            'receipt'     => $receipt,
            'interest'    => $financial['interest'],
            'letter_no'   => (int) $event->letter_no,
            'pawnDetails' => TPawnDetails::where('Receipt_Number', $receipt->Receipt_Number)->where('BC', $branch_code)->get(),
            'receiptType' => $receiptTypeModel,
            'branchDetails'    => branchDel::where('bccode', $branch_code)->first(),
            'financial' => $financial,
        ];
    }

    return view('gold_loan_notice_bulk_print', compact('letters', 'company', 'letter_no'));
}

    private function eligibleReceipts(Request $request, string $branchCode, ReceiptFinancialCalculator $calculator, int $tabLetter = 1)
    {
        $perPage = 25;
        $today = today();
        $schedule = new \App\Services\ReceiptPenaltySchedule();
        $dueSql = [1 => $schedule->letterDueSql(1), 2 => $schedule->letterDueSql(2), 3 => $schedule->letterDueSql(3)];

        $baseQuery = TPawnSum::where('BC', $branchCode)
            ->where('IsRedeemed', 0)
            ->where('isForfeit', 0);

        if ($request->filled('receipt_type')) {
            $baseQuery->where('Receipt_Type', $request->receipt_type);
        }
        if ($request->filled('receipt_number')) {
            $baseQuery->where('Receipt_Number', $request->receipt_number);
        }

        // Tab-specific filter
        if ($tabLetter === 1) {
            $baseQuery->whereRaw($dueSql[1].' <= ?', [$today->toDateString()])
                ->where(fn ($f) => $f->whereNull('is_letter_1')->orWhere('is_letter_1', 0));
        } elseif ($tabLetter === 2) {
            $baseQuery->where('is_letter_1', 1)
                ->where(fn ($f) => $f->whereNull('is_letter_2')->orWhere('is_letter_2', 0))
                ->whereRaw($dueSql[2].' <= ?', [$today->toDateString()]);
        } elseif ($tabLetter === 3) {
            $baseQuery->where('is_letter_2', 1)
                ->where(fn ($f) => $f->whereNull('is_letter_3')->orWhere('is_letter_3', 0))
                ->whereRaw($dueSql[3].' <= ?', [$today->toDateString()]);
        }

        $paginated = $baseQuery->orderBy('Final_date')->paginate($perPage)->withQueryString();

        $receipts = $paginated->getCollection();

        // Batch load customers to prevent N+1 queries
        $nics = $receipts->pluck('Customer_NIC')->filter()->unique();
        $customers = Customer::where('BC', $branchCode)->whereIn('NIC', $nics)->get()->keyBy('NIC');

        // Batch load t_pawn_trans postage charges to prevent N+1 queries
        $receiptNumbers = $receipts->pluck('Receipt_Number')->filter()->unique();
        $transPostages = array_fill_keys($receiptNumbers->map(fn ($n) => (string) $n)->all(), 0.0);
        $foundPostages = DB::table('t_pawn_trans')
            ->whereIn('code', $receiptNumbers)
            ->where('BC', $branchCode)
            ->whereNotNull('Postage_charge')
            ->where('Postage_charge', '>', 0)
            ->orderByDesc('id')
            ->get()
            ->groupBy('code');
        foreach ($foundPostages as $code => $rows) {
            $transPostages[(string) $code] = (float) $rows->first()->Postage_charge;
        }
        $calculator->setPreloadedPostageCharges($transPostages);

        $receipts->each(function (TPawnSum $receipt) use ($calculator, $schedule, $customers, $tabLetter) {
            $this->applyCurrentCustomerContact($receipt, $customers->get($receipt->Customer_NIC));
            $receipt->setAttribute('financial_breakdown', $calculator->calculate($receipt));
            $receipt->setAttribute('next_letter_no', $tabLetter);
            $receipt->setAttribute('next_letter_due_date', $schedule->letterDueDate($receipt, $tabLetter)->toDateString());
        });

        $paginated->setCollection($receipts);
        return $paginated;
    }

    private function applyCurrentCustomerContact(TPawnSum $receipt, ?Customer $customer = null): void
    {
        $customer = $customer ?? Customer::where('NIC', $receipt->Customer_NIC)->where('BC', $receipt->BC)->first();
        if (!$customer) {
            return;
        }
        $receipt->Customer_Name = $customer->Name ?: trim(implode(' ', array_filter([
            $customer->First_name, $customer->Middle_name, $customer->Last_name,
        ])));
        $receipt->Customer_Address = $customer->Address_1;
        $receipt->Customer_Phone = $customer->Contact_1;
    }

}
