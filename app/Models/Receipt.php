<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $fillable=[
        'Customer_NIC',
        'Customer_Address',
        'Receipt_Type',
        'Receipt_Number',
        'Date',
        'Category',
        'Articles',
        'Condition',
        'Karatage',
        'Weight',
        'QTY',
        'Value'
    ];
}
