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
        Schema::create('cancel__pawnings', function (Blueprint $table) {
            $table->id();
            $table->String('S_No')->unique();
            $table->String('Bill_type')->unique();
            $table->String('Bill_No');
            $table->date('Date');
            $table->String('Cus_Name');
            $table->String('Cus_Add');
            $table->String('NIC');
            $table->String('Weight');
            $table->String('Amount');
            $table->String('Cancel_By');
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
        Schema::dropIfExists('cancel__pawnings');
    }
};
