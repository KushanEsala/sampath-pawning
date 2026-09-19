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
        Schema::create('t_pawn_details', function (Blueprint $table) {
            $table->id();
            $table->string('Receipt_Number')->nullable();
            $table->string('Receipt_Type')->nullable();
            $table->string('Category')->nullable();
            $table->string('Articles')->nullable();
            $table->String('Condition')->nullable();
            $table->String('Karatage')->nullable();
            $table->integer('Weight')->nullable();
            $table->integer('QTY')->nullable();
            $table->integer('Value')->nullable();
            // $table->string('Amount')->nullable();
            // $table->string('Total_Amount')->nullable();
            $table->string('Date')->nullable();
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
        Schema::dropIfExists('t_pawn_details');
    }
};
