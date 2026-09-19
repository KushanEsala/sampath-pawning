<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TDailyBalance extends Model
{
    use HasFactory;

    protected $table = 't_daily_balances';

    protected $fillable = [
        'balance_date',
        'branch_code',
        'beginning_balance',
        'total_debit',
        'total_credit',
        'ending_balance',
        'created_by',
        'status',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'beginning_balance' => 'decimal:2',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'ending_balance' => 'decimal:2',
    ];

    /**
     * Get the next day's beginning balance (current ending balance)
     */
    public function getNextDayBeginningBalance()
    {
        return $this->ending_balance;
    }

    /**
     * Check if day is closed
     */
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Close the day
     */
    public function closeDay()
    {
        $this->status = 'closed';
        $this->save();
    }

    /**
     * Reopen the day
     */
    public function reopenDay()
    {
        $this->status = 'open';
        $this->save();
    }
}
