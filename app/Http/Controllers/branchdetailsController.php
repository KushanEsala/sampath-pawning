<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\branchDel;

class branchdetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branch =branchDel::latest()->paginate(6);
        // $branch =branchDel::all();
        return view('branchdetails')->with("branchDel" , $branch);
    }

    function add_branch(Request $request){
        $request->validate([
            'bccode'=>'required | max:5 | unique:branch_dels',
            'name'=>'required | max:20 ',
            'address'=>'required | max:40 ',
            'contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            'contact2'=>['max:10'],
            'date'=>'required',
        ]);
        // $request->validate(
        //     [
        //         'up_bccode'=>'required | max:5 | unique:branch_dels',
        //         'up_name'=>'required | max:20 ',
        //         'up_address'=>'required | max:40 ',
        //         'up_contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
        //         'up_contact2'=>['max:10'],
        //         'up_date'=>'required',
        //     ],
        //     [
        //         'up_bccode.required' => 'BC Code is Required',
        //         'up_bccode.unique' => 'Branch Already Exists',
        //         'up_bccode.max' => 'BC Code Maximum Characters is 5',
        //         'up_name.required' => 'Branch Name is Required',
        //         'up_name.max' => 'Branch Name Maximum Characters is 20',
        //     ]
        // );

        $branch = new branchDel;
        $branch->bccode = $request->bccode;
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->contact1 = $request->contact1;
        $branch->contact2 = $request->contact2;
        $branch->date = $request->date;
        $branch->save();
        return redirect()->back()->with("Add_branch",$branch);
    }


// .....................Add Branch using Ajax.........................
    public function create(Request $request)
    {
        $request->validate([
            'bccode'=>'required | max:5 | unique:branch_dels',
            'name'=>'required | max:20 ',
            'address'=>'required | max:40 ',
            'contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            'contact2'=>['max:10'],
            'date'=>'required',
        ]);

        $branch = new branchDel;
        $branch->bccode = $request->bccode;
        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->contact1 = $request->contact1;
        $branch->contact2 = $request->contact2;
        $branch->date = $request->date;
        $branch->save();
        return response()->json([
            'status'=>'success',
        ]);
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }

// ............update using ajax.................
    public function update(Request $request){
        $request->validate(
        [
            'up_bccode'=>'required | max:5 | unique:branch_dels,name,'.$request->up_id,
            'up_name'=>'required | max:20, name,',
            'up_address'=>'required | max:40 ',
            'up_contact1'=>['required','max:10', 'regex:/^0\d{9,}$/'],
            'up_contact2'=>['max:10'],
            'up_date'=>'required',
        ]);

        branchDel::where('id',$request->up_id)->update([
            'bccode'=>$request->up_bccode,
            'name'=>$request->up_name,
            'address'=>$request->up_address,
            'contact1'=>$request->up_contact1,
            'contact2'=>$request->up_contact2,
            'date'=>$request->up_date,
        ]);

        return response()->json([
            'status'=>'success',
        ]);
    }

// ............delete using ajax.................
    public function delete(Request $request){
        branchDel::find($request->branch_id)->delete();
        return response()->json([
            'status'=>'success',
        ]);
    }

    public function destroy($id)
    {
        $data = branchDel::find($id);
        $data->delete();
        return redirect()->back()->with("delete", "Branch deleted");
    }

    // ............pagination using ajax.................
    public function pagination(Request $request){
        $branchDel = branchDel::latest()->paginate(5);
        return view('branch_details_pagination')->with("branchDel",$branchDel)->render();
    }


    // ............search using ajax.................
    public function search(Request $request){
        $branchDel = branchDel::where('name', 'like', '%'.$request->search_string.'%')
        ->orWhere('bccode','like','%'.$request->search_string.'%')
        ->orWhere('address','like','%'.$request->search_string.'%')
        ->orWhere('contact1','like','%'.$request->search_string.'%')
        ->orWhere('contact2','like','%'.$request->search_string.'%')
        ->orWhere('date','like','%'.$request->search_string.'%')
        ->orderBy('bccode','desc')
        ->paginate(5);

        if($branchDel->count() >= 1){
            return view('branch_details_pagination')->with("branchDel",$branchDel)->render();
        }else{
            return response()->json([
                'status'=>'not_found'
            ]);
        }
    }
}
