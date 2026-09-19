<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TGentralReceipt extends Model
{
    use HasFactory;
  protected $fillable = [
    'date',
    'cramount',
    'crcode',
    'dramount',
    'drcode',
    'description',
    'amount',
    'OC',
    'BC',
    'status',        // Add this
    'update_date'    // Add this
];
}