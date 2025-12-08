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
        // Add 'pending_acceptance' to the class_status ENUM
        DB::statement("ALTER TABLE `assigned_daily_data` MODIFY COLUMN `class_status` ENUM('not_assigned', 'partially_assigned', 'pending_acceptance', 'fully_assigned', 'cancelled') NOT NULL DEFAULT 'not_assigned'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending_acceptance' from the class_status ENUM
        DB::statement("ALTER TABLE `assigned_daily_data` MODIFY COLUMN `class_status` ENUM('not_assigned', 'partially_assigned', 'fully_assigned', 'cancelled') NOT NULL DEFAULT 'not_assigned'");
    }
};
