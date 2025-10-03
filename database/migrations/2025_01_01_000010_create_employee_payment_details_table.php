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
        Schema::create('employee_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_payment_id');
            $table->string('field_name'); // 'account_number', 'bank_name', 'paypal_email', etc.
            $table->text('field_value'); // The actual value
            $table->timestamps();
            
            // Foreign key
            $table->foreign('employee_payment_id')->references('id')->on('employee_payment_information')->onDelete('cascade');
            
            // Indexes
            $table->index('employee_payment_id');
            $table->index(['employee_payment_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payment_details');
    }
};
