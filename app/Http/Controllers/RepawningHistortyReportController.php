<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepawningHistortyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
{
    // Validate date inputs
    $request->validate([
        'from_date' => 'nullable|date',
        'to_date' => 'nullable|date|after_or_equal:from_date',
    ]);

    $branch_code = auth()->user()->BC;

    // Build query with join and GROUP BY
    $query = DB::table('t_repawning_sums as rs')
        ->join('t_pawn_sums as ps', 'rs.Receipt_Number', '=', 'ps.Receipt_Number')
        ->where('rs.BC', $branch_code)
        ->select([
            'rs.Receipt_Number',
            'rs.Invoice_Number',
            'rs.Redeem_Date',
            'rs.Original_Pawn_Amount',
            'rs.Payable_Total',
            // From t_pawn_sums table
            DB::raw('MAX(ps.Pawn_Date) as Pawn_Date'),
            DB::raw('MAX(ps.Customer_NIC) as Customer_NIC'),
            DB::raw('MAX(ps.Customer_Name) as Customer_Name'),
            DB::raw('MAX(ps.Customer_Address) as Customer_Address'),
            DB::raw('MAX(ps.Customer_Phone) as Customer_Phone'),
            DB::raw('MAX(ps.Interest_Rate) as Interest_Rate')
        ])
        ->groupBy(
            'rs.Receipt_Number',
            'rs.Invoice_Number',
            'rs.Redeem_Date',
            'rs.Original_Pawn_Amount',
            'rs.Payable_Total'
        );

    // Apply date filters if provided
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('rs.Redeem_Date', [
            $request->from_date,
            $request->to_date
        ]);
    }

    // Get results ordered by date
    $repawnings = $query->orderBy('rs.Redeem_Date', 'desc')->get();

    return view('repawningHistortyReport', compact('repawnings'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function create(Request $request)
    {

        $branch_code = auth()->user()->BC;

        $query = DB::table('t_pawn_payments')->where('BC', $branch_code);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('Redeem_Date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $partPayments = $query->orderBy('Redeem_Date', 'desc')->get();

        return view('PartpaymentHistortyReport', compact('partPayments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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