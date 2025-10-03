<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id('supID');
            $table->string('sfname', 50);
            $table->string('smname', 50)->nullable();
            $table->string('slname', 50);
            $table->date('birth_date')->nullable();
            $table->string('semail', 100)->unique();
            $table->string('sconNum', 20)->nullable();
            $table->string('assigned_account', 100)->nullable();
            $table->string('srole', 50)->nullable();
            $table->string('saddress', 500)->nullable();
            $table->string('steams', 100)->nullable();
            $table->string('sshift', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};
