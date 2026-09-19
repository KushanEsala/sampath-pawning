<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MMainAccountType;
use App\Models\MMainCategory;
use Datatables;

class Account_typeController extends Controller
{
    public function index()
    {
        $itemCategory = MMainAccountType::all();
        $Category = MMainCategory::all();

        return view('account_type')
        ->with("Category" , $Category)
        ->with("CategoryData" , $itemCategory);

    }


    public function createAccountype(Request $request)
    {
        $request->validate([
            'code'=>'required ',
            'categoryName'=>'required | max:80',
            'description'=>'required | max:80',
        ]);
        $Accountype = new MMainAccountType;
        $Accountype->code = $request->code;
        $Accountype->category = $request->categoryName;
        $Accountype->description = $request->description;
        $Accountype->save();

        return response()->json([
            'status'=>'success',
        ]);
    }


    public function deleteAccountype(Request $request)
    {
        MMainAccountType::find($request->id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }



    public function updateAccountype(Request $request)
    {
        $request->validate([
            'up_code'=>'required | max:80',
            'up_categoryName'=>'required | max:80',
            'up_description'=>'required | max:80',
        ]);
        MMainAccountType::where('id',$request->up_id)->update([
            'code'=> $request->up_code,
            'category'=> $request->up_categoryName,
            'description'=> $request->up_description,

        ]);
        return response()->json([
            'status'=>'success',
        ]);
    }



}

