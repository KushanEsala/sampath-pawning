<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPawnDetails extends Model
{
    use HasFactory;
    protected $fillable=[
        'Receipt_Number',
        'Receipt_Type',
        'Category',
        'Articles',
        'Condition',
        'Karatage',
        'Weight',
        'Total_Weight',
        'QTY',
        'Value',
        'Amount',
        'Total_Amount',
        'Date',
        'OC',
        'BC'
    ];

}
