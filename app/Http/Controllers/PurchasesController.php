<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchasesController extends Controller
{

    function viewAddPurchases(){
        return view('AddPurchases');
    }


    function createPurchases(){}
}
