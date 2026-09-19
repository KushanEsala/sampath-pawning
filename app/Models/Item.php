<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
    'category', 'Item_code', 'Bar_code', 'Item_description', 'Brand',
    'Color', 'Make', 'image', 'purchasePrice', 'creditprice',
    'saleprice', 'Percentage', 'Branch', 'BC','QTY','Total_Weight','Weight','IntoItem','Receipt_Number'
];

}