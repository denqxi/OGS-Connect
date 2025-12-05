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
        Schema::table('supervisors', function (Blueprint $table) {
            // Remove shift column if it exists
            if (Schema::hasColumn('supervisors', 'shift')) {
                $table->dropColumn('shift');
            }
            
            // Add work preference columns similar to tutors
            if (!Schema::hasColumn('supervisors', 'start_time')) {
                $table->time('start_time')->nullable()->after('ms_teams');
            }
            if (!Schema::hasColumn('supervisors', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('supervisors', 'days_available')) {
                $table->json('days_available')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('supervisors', 'timezone')) {
                $table->string('timezone', 50)->default('UTC')->after('days_available');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            // Restore shift column
            if (!Schema::hasColumn('supervisors', 'shift')) {
                $table->string('shift', 75)->nullable()->after('ms_teams');
            }
            
            // Remove work preference columns
            if (Schema::hasColumn('supervisors', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('supervisors', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('supervisors', 'days_available')) {
                $table->dropColumn('days_available');
            }
            if (Schema::hasColumn('supervisors', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
};
