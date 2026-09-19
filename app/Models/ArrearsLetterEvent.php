<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArrearsLetterEvent extends Model
{
    protected $fillable = [
        'pawn_sum_id', 'BC', 'receipt_number', 'cycle_no', 'letter_no',
        'due_date', 'issued_at', 'postage_charge', 'service_charge',
        'issued_by', 'reprint_count', 'last_reprinted_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'last_reprinted_at' => 'datetime',
        'postage_charge' => 'decimal:2',
        'service_charge' => 'decimal:2',
    ];
}

