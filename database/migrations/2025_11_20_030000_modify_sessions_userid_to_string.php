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
        // Change the sessions.user_id column to a string to support non-integer user IDs
        // Use raw SQL to avoid the doctrine/dbal dependency for now.
        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` VARCHAR(100) NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to unsigned BIGINT (may fail if non-numeric values exist)
        DB::statement('ALTER TABLE `sessions` MODIFY `user_id` BIGINT UNSIGNED NULL;');
    }
};
