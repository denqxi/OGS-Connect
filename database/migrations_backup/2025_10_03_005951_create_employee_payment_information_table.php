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
        Schema::create('employee_payment_information', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id'); // Can be tutorID or supID
            $table->enum('employee_type', ['tutor', 'supervisor']);
            $table->enum('payment_method', [
                'gcash', 
                'paymaya', 
                'bdo', 
                'bpi', 
                'metrobank', 
                'landbank', 
                'unionbank', 
                'paypal', 
                'remittance', 
                'cash'
            ]);
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('paypal_email')->nullable();
            $table->string('gcash_number')->nullable();
            $table->string('paymaya_number')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->enum('payment_frequency', ['hourly', 'daily', 'weekly', 'monthly'])->default('monthly');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['employee_id', 'employee_type']);
            $table->unique(['employee_id', 'employee_type']); // One payment info per employee
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payment_information');
    }
};