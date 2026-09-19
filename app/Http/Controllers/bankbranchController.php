<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\BankBranch;
use App\Models\BankDetails;
use Datatables;
 
class bankbranchController extends Controller
{
    public function index()
    {
        if(request()->ajax()) {
            return datatables()->of(BankBranch::select('*'))
            ->addColumn('action', 'BankBranch-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        $BankDetails = BankDetails::all(); 
        return view('Bank_Branch')
        -> with("itemCategory", $BankDetails);
    }
 
    public function mainstore(Request $request)
    {  
  
        $BankBranchId = $request->id;
  
        $BankBranch  =   BankBranch::updateOrCreate(
                    [
                     'id' => $BankBranchId
                    ],
                    [
                    'department' => $request->department, 
                    'category_code' => $request->category_code,
                    'category_description' => $request->category_description,
                    ]);    
                          
        return Response()->json($BankBranch);
    }
 
    public function mainedit(Request $request)
    {   
        $where = array('id' => $request->id);
        $BankBranch  = BankBranch::where($where)->first();
       
        return Response()->json($BankBranch);
    }
 
    public function maindestroy(Request $request)
    {
        $BankBranch = BankBranch::where('id',$request->id)->delete();
       
        return Response()->json($BankBranch);
    }


}