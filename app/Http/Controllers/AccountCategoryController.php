<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MMainCategory;
use Datatables;

class AccountCategoryController extends Controller
{
    public function index()
    {

        $itemCategory = MMainCategory::all();
        
        return view('account_category')
        ->with("itemCategoryData" , $itemCategory);

    }


    public function createCateAccount(Request $request)
    {
        $request->validate([
            'code'=>'required | max:80 |unique:categories,category',
            'categoryName'=>'required | max:80 |unique:categories,category',
        ]);
        $Category = new MMainCategory;
        $Category->code = $request->code;
        $Category->category = $request->categoryName;
        $Category->save();

        return response()->json([
            'status'=>'success',
        ]);
    }


    public function deleteCateAccount(Request $request)
    {
        MMainCategory::find($request->id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }



    public function updateCateAccount(Request $request)
    {
        $request->validate([
            'up_code'=>'required | max:80',
            'up_categoryName'=>'required | max:80',
        ]);
        MMainCategory::where('id',$request->up_id)->update([
            'code'=> $request->up_code,
            'category'=> $request->up_categoryName,

        ]);
        return response()->json([
            'status'=>'success',
        ]);
    }



}
