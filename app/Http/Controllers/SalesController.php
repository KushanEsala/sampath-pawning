<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Item;
use App\Models\TInvoiceSum;
use App\Models\TInvoiceDeils;
use App\Models\TItemMovement;
use App\Models\branchDel;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   

public function index(Request $request)
{
        $branch_code = auth()->user()->BC;
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

    $query = DB::table('t_invoice_sums as sums')
        ->join('t_invoice_deils as deils', 'sums.Invoice_no', '=', 'deils.Invoice_no')
        ->select(
            'sums.Invoice_no',
            'sums.Invoice_date',
            'sums.Customer_Name',
            'sums.Customer_NIC',
            'sums.Net_Amount',
            'deils.Item_code',
            'deils.Item_description',
            'deils.QTY',
            'deils.Unit_price',
            'deils.Net_value'
        )
        ->where('sums.BC', $branch_code);

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('sums.Invoice_date', [$request->from_date, $request->to_date]);
    }

    $data = $query->get();

    return view('salesInvoiceReport', [
        'data' => $data,
        'from_date' => $request->from_date,
        'to_date' => $request->to_date,
    ]);
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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