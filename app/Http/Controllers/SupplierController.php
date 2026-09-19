<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function showSuppliers(){

        return view('suppliers');
    }


    public function showAddSuppliers(){

        return view('addSuppliersForm');
    }
}
