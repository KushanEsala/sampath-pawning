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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable()->default('admin');
            $table->string('name')->default('admin');
            $table->string('email')->unique()->default('admin@gmail.com');
            $table->string('role')->nullable()->default('admin');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->default('$2y$10$uzh5PdNAmSNq4L1KrCBwl.7CsPAgEPTK58f/77I24yNANVfBQc25.');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
