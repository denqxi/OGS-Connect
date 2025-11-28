<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update final_status enum to include all statuses used in the app
        // We use raw SQL to avoid depending on doctrine/dbal for enum changes
        DB::statement("ALTER TABLE `archived_applications` MODIFY `final_status` ENUM('recommended','not_recommended','pending','declined','no_answer','no_answer_3_attempts','re_schedule') NOT NULL DEFAULT 'pending';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the previous enum definition
        DB::statement("ALTER TABLE `archived_applications` MODIFY `final_status` ENUM('recommended','not_recommended','pending') NOT NULL DEFAULT 'pending';");
    }
};
