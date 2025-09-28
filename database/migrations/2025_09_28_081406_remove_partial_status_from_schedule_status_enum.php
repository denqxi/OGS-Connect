<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // First, update any existing 'partial' records to 'draft'
            DB::table('daily_data')
                ->where('schedule_status', 'partial')
                ->update(['schedule_status' => 'draft']);
            
            // Then alter the enum to remove 'partial'
            $table->enum('schedule_status', ['draft', 'final'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Restore the original enum with 'partial'
            $table->enum('schedule_status', ['draft', 'partial', 'final'])->default('draft')->change();
        });
    }
};
