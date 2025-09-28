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
            // Temporarily change schedule_status to VARCHAR(20)
            DB::statement("ALTER TABLE daily_data MODIFY schedule_status VARCHAR(20) NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Revert to ENUM (previous state, adjust as needed)
            DB::statement("ALTER TABLE daily_data MODIFY schedule_status ENUM('tentative', 'final', 'cancelled') NULL DEFAULT 'tentative'");
        });
    }
};
