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
        // If the column already exists and is an enum / restricted type,
        // change it to a VARCHAR to allow values like 'rejected'.
        if (Schema::hasTable('tutor_work_details')) {
            if (Schema::hasColumn('tutor_work_details', 'status')) {
                // Use raw SQL to avoid requiring doctrine/dbal
                DB::statement('ALTER TABLE `tutor_work_details` MODIFY `status` VARCHAR(50) NULL');
            } else {
                Schema::table('tutor_work_details', function (Blueprint $table) {
                    $table->string('status', 50)->nullable()->after('duration_minutes')->default('pending');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Best effort: leave as VARCHAR to avoid data loss. No-op.
    }
};
