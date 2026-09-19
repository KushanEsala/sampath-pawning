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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('Customer_NIC')->nullable();
            $table->string('Customer_Address')->nullable();
            $table->string('Customer_Phone')->nullable();
            $table->string('Receipt_Type')->nullable();
            $table->string('Receipt_Number')->nullable();
            $table->string('Receipt_Date')->nullable();
            $table->string('Date')->nullable();
            $table->string('Category')->nullable();
            $table->string('Articles')->nullable();
            $table->String('Condition')->nullable();
            $table->String('Karatage')->nullable();
            $table->integer('Weight')->nullable();
            $table->integer('QTY')->nullable();
            $table->integer('Value')->nullable();
            $table->timestamps();

        });
    }


    public function down()
    {
        Schema::dropIfExists('receipts');
    }
};
