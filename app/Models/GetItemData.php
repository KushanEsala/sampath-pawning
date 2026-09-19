<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GetItemData extends Model


{
    protected $table = 'get_item_data'; // optional if default naming matches

    protected $fillable = [
        'category',
        'Item_code',
        'Bar_code',
        'Item_description',
        'Brand',
        'Karatage',
        'purchasePrice',
        'saleprice',
        'Total_Weight',
        'Weight',
        'QTY',
        'Branch',
        'BC',
        'Receipt_Number'
    ];

    public $timestamps = true;

}