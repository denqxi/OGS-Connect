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
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->enum('payment_frequency', ['hourly', 'daily', 'weekly', 'monthly'])->default('monthly');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('payment_method_id')->references('id')->on('payment_method_details')->onDelete('restrict');
            
            // Indexes
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
