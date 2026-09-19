<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TItemMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'trans_no',
        'dDate',
        'trans_code',
        'item_code',
        'qun_in',
        'bc',
    ];
}