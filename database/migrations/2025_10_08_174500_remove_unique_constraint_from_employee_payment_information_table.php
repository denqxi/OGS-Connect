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
        Schema::table('employee_payment_information', function (Blueprint $table) {
            // Remove the unique constraint to allow multiple payment methods per employee
            $table->dropUnique(['employee_id', 'employee_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payment_information', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique(['employee_id', 'employee_type']);
        });
    }
};
