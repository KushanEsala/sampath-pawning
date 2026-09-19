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
        Schema::create('recei__adds', function (Blueprint $table) {
            $table->id();
            $table->string('receiptname');
            $table->string('rate1');
            $table->string('period1');
            $table->string('rate2');
            $table->string('period2');
            $table->string('rate3');
            $table->string('period3');
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
        Schema::dropIfExists('recei__adds');
    }
};
