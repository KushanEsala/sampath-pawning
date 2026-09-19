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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->String('Code')->unique();
            $table->String('Title');
            $table->String('Gender');
            $table->String('Name')->nullable();
            $table->String('Address_1');
            $table->String('Address_2')->nullable();
            $table->String('Contact_1');
            $table->String('Contact_2')->nullable();
            $table->String('Email');
            $table->String('NIC');
            $table->String('Driving_license')->nullable();
            $table->String('Passport')->nullable();
            $table->String('Other_identifications')->nullable();
            $table->boolean('Status')->nullable();
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
        Schema::dropIfExists('customers');
    }
};
