<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutor_assignments')) {
            // nothing to fix if table doesn't exist
            return;
        }

        Schema::table('tutor_assignments', function (Blueprint $table) {
            // ensure daily_data_id exists and has index
            if (!Schema::hasColumn('tutor_assignments', 'daily_data_id')) {
                $table->unsignedBigInteger('daily_data_id')->after('id');
            }
            if (!Schema::hasColumn('tutor_assignments', 'tutor_id')) {
                $table->unsignedBigInteger('tutor_id')->after('daily_data_id');
            }
        });

        // Try to add foreign keys; wrap in try/catch in case they already exist
        try {
            DB::statement('ALTER TABLE `tutor_assignments` ADD CONSTRAINT `tutor_assignments_daily_data_id_foreign` FOREIGN KEY (`daily_data_id`) REFERENCES `daily_data`(`id`) ON DELETE CASCADE');
        } catch (\Throwable $e) {
            // ignore if exists or cannot add
        }

        try {
            // Reference numeric primary key on `tutor` table (`tutor_id`)
            DB::statement('ALTER TABLE `tutor_assignments` ADD CONSTRAINT `tutor_assignments_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutor`(`tutor_id`) ON DELETE CASCADE');
        } catch (\Throwable $e) {
            // ignore if exists or cannot add
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tutor_assignments')) {
            return;
        }

        // Drop foreign keys if they exist
        try {
            DB::statement('ALTER TABLE `tutor_assignments` DROP FOREIGN KEY `tutor_assignments_tutor_id_foreign`');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `tutor_assignments` DROP FOREIGN KEY `tutor_assignments_daily_data_id_foreign`');
        } catch (\Throwable $e) {}
    }
};
