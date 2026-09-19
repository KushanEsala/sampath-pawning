<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TPawnDetails;
use App\Models\TPawnTrans;
use Illuminate\Support\Facades\DB;

class PawingArticleReportController extends Controller
{
    public function index(Request $request)
    {
        $branch_code = auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        // Build the query with join
        $query = TPawnDetails::select(
                't_pawn_details.*',
                't_pawn_sums.Invoice_Number',
                't_pawn_sums.Receipt_Type',
            )
            ->join('t_pawn_sums', 't_pawn_details.Receipt_Number', '=', 't_pawn_sums.Receipt_Number')
            ->where('t_pawn_details.BC', $branch_code);
        
        // Apply date filter if provided
        if ($fromDate && $toDate) {
            $query->whereBetween('t_pawn_details.Date', [$fromDate, $toDate]);
        }
        
        $receipts = $query->get();
        
        // Calculate totals from the filtered results
        $totalWeight = $receipts->sum('Total_Weight');
        $pawnWeight = $receipts->sum('Weight');
        $pawnqty = $receipts->sum('QTY');
        
        return view('pawingarticlereport', [
            'receipts' => $receipts,
            'totalWeight' => $totalWeight,
            'pawnWeight' => $pawnWeight,
            'pawnqty' => $pawnqty
        ]);
    }
}