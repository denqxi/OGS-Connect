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
        Schema::table('daily_data', function (Blueprint $table) {
            // If ENUM, add 'finalized' to allowed values
            DB::statement("ALTER TABLE daily_data MODIFY schedule_status ENUM('tentative', 'final', 'cancelled', 'finalized') NULL DEFAULT 'tentative'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Revert ENUM to previous values (remove 'finalized')
            DB::statement("ALTER TABLE daily_data MODIFY schedule_status ENUM('tentative', 'final', 'cancelled') NULL DEFAULT 'tentative'");
        });
    }
};
