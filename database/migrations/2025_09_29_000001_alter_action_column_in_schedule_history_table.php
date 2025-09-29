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
        Schema::table('schedule_history', function (Blueprint $table) {
            // Change 'action' column from ENUM to VARCHAR(50)
            $table->string('action', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // You may want to revert to the original ENUM if needed
        // Adjust the ENUM values as per your original schema
        Schema::table('schedule_history', function (Blueprint $table) {
            $table->enum('action', ['created', 'updated', 'finalized', 'cancelled', 'exported'])->change();
        });
    }
};
