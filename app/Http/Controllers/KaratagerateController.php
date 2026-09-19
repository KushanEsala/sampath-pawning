<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\karatage;

class KaratagerateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $karatageData = karatage::latest()->paginate(5);
        return view('Karatagerate')->with("karatageData", $karatageData);
    }



    // ------------- Create karatage using Ajex-------------
    public function create(Request $request){
        $request->validate([
            'descrption'=>'required | max:5000',
            'pawningrate'=>'required |numeric ',
            'assesrate'=>'required |numeric ',
            'marketrate'=>'required |numeric ',
        ]);

        $karatage = new karatage;
        $karatage->descrption = $request->descrption;
        $karatage->pawningrate = $request->pawningrate;
        $karatage->assesrate = $request->assesrate;
        $karatage->marketrate = $request->marketrate;
        $karatage->salepriceRate = $request->salepriceRate;
        $karatage->save();

        return response()->json([
            'status'=>'success',
        ]);
    }

    // ------------- delete karatage using Ajex-------------
    public function delete(Request $request)
    {
        karatage::find($request->karatage_id)->delete();
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


    public function update(Request $request)
    {
        $request->validate([
            'up_descrption'=>'required | max:50',
            'up_pawningrate'=>'required |numeric  ',
            'up_assesrate'=>'required |numeric ',
            'up_marketrate'=>'required |numeric ',
        ]);

        karatage::where('id',$request->up_id)->update([
            'descrption'=>$request->up_descrption,
            'pawningrate'=>$request->up_pawningrate,
            'assesrate'=>$request->up_assesrate,
            'salepriceRate'=>$request->up_salepriceRate,
        ]);

        return response()->json([
            'status'=>'success',
        ]);
    }



}