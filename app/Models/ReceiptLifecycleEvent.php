<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptLifecycleEvent extends Model
{
    protected $fillable = [
        'pawn_sum_id', 'BC', 'receipt_number', 'event_type', 'event_date',
        'amount', 'description', 'event_data', 'created_by',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'amount' => 'decimal:2',
    ];
}

