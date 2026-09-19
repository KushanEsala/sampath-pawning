<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPawnSum extends Model
{
    use HasFactory;

    protected $table = 't_pawn_sums';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'letter_1_days', 'letter_2_days', 'letter_3_days', 'forfeit_reminder_days', 'forfeit_queued_at',
        // Customer Info
        'Customer_NIC',
        'Customer_Name',
        'First_name',
        'Middle_name',
        'Last_name',
        'Customer_Address',
        'Customer_Phone',

        // Receipt Info
        'Receipt_Type',
        'Receipt_Number',
        'Invoice_Number',
        'Ticket_Number',

        // Dates
        'Date',
        'Receipt_Date',
        'Pawn_Date',
        'RePawning_date',
        'To_Date',
        'Final_date',

        // Financial
        'Amount',
        'Total_Amount',
        'Pawn_Amount',
        'RePawning_amount',
        'Interest',
        'Interest_Rate',
        'Valid_Period',

        // Weight
        'Total_Weight',
        'Pawn_Weight',

        // Status
        'IsRedeemed',
        'isForfeit',
        'is_letter_1',
        'is_letter_2',
        'is_letter_3',
        'letter_1_date',
        'letter_2_date',
        'letter_3_date',
        'letter_pay_one',
        'letter_pay_two',
        'letter_pay_three',

        // Auth
        'OC',
        'BC',

        // From recei__adds
        'receiptname',
        'documentCharges',
        'service_charge',
        'stampduty',
        'validPeriod',
        'period1',
        'rate1',
        'period2',
        'rate2',
        'period3',
        'rate3',
        'pawn_amount',
        'Postage_charge',
        's_charge_less',
        's_charge_greater',
    ];

    protected $casts = [
        'forfeit_queued_at' => 'datetime',
        'letter_1_days' => 'integer', 'letter_2_days' => 'integer',
        'letter_3_days' => 'integer', 'forfeit_reminder_days' => 'integer',
        'Receipt_Date'    => 'date',
        'Pawn_Date'       => 'date',
        'RePawning_date'  => 'date',
        'To_Date'         => 'date',
        'Final_date'      => 'date',
        'IsRedeemed'      => 'boolean',
        'isForfeit'       => 'boolean',
        'is_letter_1'     => 'boolean',
        'is_letter_2'     => 'boolean',
        'is_letter_3'     => 'boolean',
        'letter_1_date'   => 'date',
        'letter_2_date'   => 'date',
        'letter_3_date'   => 'date',
        'letter_pay_one'  => 'decimal:2',
        'letter_pay_two'  => 'decimal:2',
        'letter_pay_three'=> 'decimal:2',
        'Amount'          => 'decimal:2',
        'Total_Amount'    => 'decimal:2',
        'Pawn_Amount'     => 'decimal:2',
        'RePawning_amount'=> 'decimal:2',
        'Interest'        => 'decimal:2',
        'Interest_Rate'   => 'decimal:2',
        'documentCharges' => 'decimal:2',
        'service_charge'  => 'decimal:2',
        'stampduty'       => 'decimal:2',
        'rate1'           => 'decimal:2',
        'rate2'           => 'decimal:2',
        'rate3'           => 'decimal:2',
        'pawn_amount'     => 'decimal:2',
        'Postage_charge'  => 'decimal:2',
        's_charge_less'   => 'decimal:2',
        's_charge_greater'=> 'decimal:2',
    ];
}
