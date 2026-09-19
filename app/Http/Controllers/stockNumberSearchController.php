<?php
 
 namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\DB;
 use App\Services\ReceiptTypeResolver;
 
 class StockNumberSearchController extends Controller
 {
     /**
      * Show search page
      */
     public function index()
     {
         $results = collect();
         $results_Details = collect();
         $results_transtion = collect();
         $Receipt_Type = collect();
 
         return view('stocknumbersearch', compact('results', 'results_Details', 'results_transtion','Receipt_Type'));
     }
 
     /**
      * Search receipt by stock number
      */
     public function SearchReceipt(Request $request, ?ReceiptTypeResolver $resolver = null)
    {
        $resolver = $resolver ?? app(ReceiptTypeResolver::class);
        $request->validate([
             'Stock_Number' => 'required|string'
         ]);
 
         $stockNumber = $request->Stock_Number;
         $branch_code = auth()->user()->BC;
 
         // Step 1: Query t_pawn_sums by Invoice_Number
         $results = DB::table('t_pawn_sums')
             ->where('Invoice_Number', $stockNumber)
             ->where('BC', $branch_code)
             ->get();
 
         // Step 2: Initialize empty collections
         $results_Details = collect();
         $results_transtion = collect();
         $Receipt_Type = collect();
 
         // Step 3: If we found records, get all Receipt_Numbers and query other tables
         if ($results->count() > 0) {
             // Get all Receipt_Numbers from the results
             $receiptNumbers = $results->pluck('Receipt_Number')->toArray();
             $receiptReceipt_Type = $results->pluck('Receipt_Type')->toArray();
 
             // Query t_pawn_details using all Receipt_Numbers
             $results_Details = DB::table('t_pawn_details')
                 ->whereIn('Receipt_Number', $receiptNumbers)
                 ->where('BC', $branch_code)
                 ->get();
 
             // Query t_pawn_trans using all Receipt_Numbers
             $results_transtion = DB::table('t_pawn_trans')
                 ->whereIn('code', $receiptNumbers)
                 ->where('BC', $branch_code)
                 ->get();
 
             $Receipt_Type = $resolver->resolveForReceiptCollection($results->first(), $receiptReceipt_Type[0] ?? null);
         }
 
         return view('stocknumbersearch', compact('results', 'results_Details', 'results_transtion','Receipt_Type'));
     }
 }