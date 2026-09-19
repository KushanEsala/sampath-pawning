<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\itemCondition;

class ItemConditionController extends Controller
{

    public function index()
    {
        $itemcondition = itemCondition::all();
        return view('itemCondition')->with("itemcondition" , $itemcondition);
    }


    public function add_itemcondition(Request $request)
    {
        $request->validate([
            'conditionsName'=>'required | max:20 ',
        ]);

       $itemcondition = new itemCondition;
       $itemcondition->conditionsName = $request->conditionsName;
       $itemcondition->save();
       return redirect()->back()->with("itemcondition" , $itemcondition);

    }


    public function create(Request $request)
    {
        $request->validate([
            'conditionsName'=>'required | max:50 |unique:item_conditions,conditionsName',
        ]);

        $itemcondition = new itemCondition;
        $itemcondition->conditionsName = $request->conditionsName;
        $itemcondition->save();

        return response()->json([
            'status'=>'success',
        ]);
    }


    public function store(Request $request)
    {

    }


    public function show($id)
    {

    }


    public function edit($id)
    {

    }

    public function update(Request $request)
    {
        $request->validate([
            'up_conditionsName'=>'required | max:50',
        ]);

        itemCondition::where('id',$request->up_id)->update([
            'conditionsName'=>$request->up_conditionsName,
        ]);




        return response()->json([
            'status'=>'success',
        ]);
    }

    public function destroy($id)
    {
        $data = itemCondition::find($id);
        $data->delete();
        return redirect()->back()->with("delete", "Receipt deleted");
    }

    // ------------- delete using Ajex-------------
    public function delete(Request $request)
    {
        itemCondition::find($request->id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }
}
