<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPawnTrans extends Model
{
    use HasFactory;
    protected $fillable = ['code','trans_no','trans_type','trans_amount','Cr_amount','Dr_amount'];
}
