<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch_code =auth()->user()->BC;
        $maxReceiptNo = Expenses::where('BC',$branch_code)
                        ->orderBy('expense_no','desc')
                        ->value('expense_no');
        $maxReceiptNos = str_pad($maxReceiptNo, 4, '0', STR_PAD_LEFT);

        return view('addExpense')
        ->with("maxReceipt", $maxReceiptNos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $post = new Expenses;
            $post->expense_no = $request->expense_no;
            $post->date = $request->date;
            $post->description = $request->expense_type;
            $post->expense_note = $request->expense_note;
            $post->amount = $request->amount;
            $post->OC = auth()->user()->username;
            $post->BC = auth()->user()->BC;
            $post->save();
           return back()
            ->with('done', 'Expense has been added');
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeExpense(Request $request)
    {
        {
            $post = new Expenses;
            $post->Expense_no = $request->Expense_no;
            $post->Expense_date = $request->Expense_date;
            $post->ExpenseType = $request->ExpenseType;
            $post->Expense_note = $request->Expense_note;
            $post->Expense_Amount = $request->Expense_Amount;
            $post->save();
            return redirect('addExpense')->with('status', 'Expense Form Data Has Been inserted');
        }
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
