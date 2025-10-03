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
            $table->string('field_name'); // e.g., 'gcash_number', 'account_number', 'account_name'
            $table->string('field_value'); // The actual value
            $table->timestamps();
            
            // Foreign key
            $table->foreign('employee_payment_id')->references('id')->on('employee_payment_information')->onDelete('cascade');
            
            // Indexes
            $table->index('employee_payment_id');
            $table->index('field_name');
            
            // Prevent duplicate field entries for same payment record
            $table->unique(['employee_payment_id', 'field_name']);
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
