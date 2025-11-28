<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename final_status to status and ensure enum includes all app statuses
        DB::statement("ALTER TABLE `archived_applications` CHANGE `final_status` `status` ENUM('recommended','not_recommended','pending','declined','no_answer','no_answer_3_attempts','re_schedule') NOT NULL DEFAULT 'pending';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert column name back to final_status (may fail if rows have values not in the original enum)
        DB::statement("ALTER TABLE `archived_applications` CHANGE `status` `final_status` ENUM('recommended','not_recommended','pending') NOT NULL DEFAULT 'pending';");
    }
};
