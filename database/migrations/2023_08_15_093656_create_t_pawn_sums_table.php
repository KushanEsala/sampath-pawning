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
        Schema::create('t_pawn_sums', function (Blueprint $table) {
            $table->id();
            $table->string('Customer_NIC')->nullable();
            $table->string('Customer_Name')->nullable();
            $table->string('Customer_Address')->nullable();
            $table->string('Customer_Phone')->nullable();
            $table->string('Receipt_Type')->nullable();
            $table->string('Receipt_Number')->nullable();
            $table->string('Receipt_Date')->nullable();
            $table->float('Amount')->nullable();
            $table->float('Total_Amount')->nullable();  
            $table->float('Interest')->nullable();
            $table->boolean('IsRedeemed')->nullable();
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
        Schema::dropIfExists('t_pawn_sums');
    }
};
