<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TOpeningPawnSum extends Model
{
    use HasFactory;
    protected $fillable=[
        'Customer_NIC',
        'Customer_Name',
        'Customer_Address',
        'Customer_Phone',
        'Receipt_Type',
        'Receipt_Number',
        'Invoice_Number',
        
        'Date',
        'Amount',
        'Total_Amount',
        'OC',
        'BC',
        'BC'
    ];
}
