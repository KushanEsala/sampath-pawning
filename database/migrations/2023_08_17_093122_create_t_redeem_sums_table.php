<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_redeem_sums', function (Blueprint $table) {
            $table->id();
            $table->string('Receipt_Number')->nullable();
            $table->string('Redeem_Date')->nullable();
            $table->string('Redeem_Number')->nullable();

            $table->float('Original_Pawn_Amount')->nullable();
            $table->float('Payable_Pawn_Amount')->nullable();
            $table->float('Paid_Interest')->nullable();
            $table->float('Payable_Interest')->nullable();
            $table->float('Stamp_Fee')->nullable();
            $table->float('Document_Charges')->nullable();
            $table->float('Advance_Balance')->nullable();
            $table->float('Discount')->nullable();
            $table->float('Payable_Total')->nullable();

            $table->String('OC')->nullable();
            $table->String('BC')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_redeem_sums');
    }
};
