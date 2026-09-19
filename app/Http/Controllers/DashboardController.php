<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function showCustomers (){
        return view('customers');
    }


  
}

// use App\Models\Order; // Assuming Order is your Eloquent model for orders

// public function dashboard()
// {
//     $orders = Order::all(); // Retrieve all orders, you might want to adjust the query as needed
//     return view('dashboard', compact('orders'));
// }
