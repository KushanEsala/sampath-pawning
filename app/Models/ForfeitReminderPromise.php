<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForfeitReminderPromise extends Model
{
    protected $fillable = [
        'pawn_sum_id', 'BC', 'receipt_number', 'cycle_no', 'promise_date',
        'remark', 'status', 'previous_promise_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'promise_date' => 'date',
    ];
}

