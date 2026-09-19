<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MChartofAccount extends Model
{
    use HasFactory;
    protected $fillable = ['account','accountsub','code','description','opening_balance','controlaccount','bankaccount', 'BC','OC'
                        ];
}
