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
        Schema::create('advance__payments', function (Blueprint $table) {
            $table->id();
            $table->String('S_No')->unique();
            $table->String('Bill_type')->unique();
            $table->String('Bill_No');
            $table->String('Cus_Name');
            $table->String('Cus_Address');
            $table->String('NIC');
            $table->String('Mobile');
            $table->String('Total_Weight');
            $table->String('Paid_Date_Time');
            $table->String('Amount');
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
        Schema::dropIfExists('advance__payments');
    }
};
