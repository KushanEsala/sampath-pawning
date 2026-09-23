<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TOpeningPawnSum extends Model
{
    use HasFactory;
    protected $fillable=[
        'is_blocked', 'blocked_at', 'blocked_by', 'block_reason',
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

    protected $casts = [
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];
}
