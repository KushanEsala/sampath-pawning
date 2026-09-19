<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Datatables;

class CompanyController extends Controller
{
    public function index()
    {
        if(request()->ajax()) {
            return datatables()->of(Company::select('*'))
            ->addColumn('action', 'Company-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return view('Company');
    }

    public function Companystore(Request $request)
    {
        $CompanyId = $request->id;

        $request->validate([
            'co_code' => ' required | max:10 | unique:companies',
            'name' => ' required | max:60',
            'address' => ' max:255',
            'co_number' => ['required','max:10','regex:/^0\d{9,}$/'],
            'fax_number' => 'max:10',
            'email' => 'max:60'
        ],[
            'co_code' => ' The Company Code field is required oe not unique',
            'name' => ' The Company Name field is required',
            'address' => ' The Address field is required',
            'co_number' => ' The Contact field format is required '

        ]);


        $Company   =   Company::updateOrCreate(
                    [
                     'id' => $CompanyId
                    ],
                    [
                    'co_code' => $request->co_code,
                    'name' => $request->name,
                    'address' => $request->address,
                    'co_number' => $request->co_number,
                    'fax_number' => $request->fax_number,
                    'email' => $request->email
                    ]);

        return Response()->json($Company);
    }

    public function Companyedit(Request $request)
    {
        $where = array('id' => $request->id);
        $Company  = Company::where($where)->first();

        return Response()->json($Company);
    }

    public function Companydestroy(Request $request)
    {
        $Company = Company::where('id',$request->id)->delete();

        return Response()->json($Company);
    }
}
