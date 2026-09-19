<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;  // Make sure this matches your model namespace
class ItemCreateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index()
{
    $branch_code = auth()->user()->BC;

    // Retrieve the items using get()
      $items = Item::where('BC', $branch_code)
                 ->where('IntoItem', 0)
                 ->where('SaleIsItem', 0)
                 ->get();

    // Pass the retrieved items to the view
    return view('createItem')->with('items', $items);
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function show($id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
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
     public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        // Validate incoming data as needed
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'Item_description' => 'required|string|max:255',
            'Item_code' => 'required|max:255',
            'QTY' => 'required|max:50',
            'Brand' => 'required',
            'Make' => 'required',
            'purchasePrice' => 'required',
            'saleprice' => 'required',
            'Total_Weight' => 'required',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    // Delete the item (called from AJAX DELETE)
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}