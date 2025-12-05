<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Changes tutor_accounts.tutor_id to reference tutor.tutor_id (integer) instead of tutorID (string)
     * 2. Analyzes if tutor_accounts is redundant with work_preferences
     * 3. If timezone is the only unique data, adds it to work_preferences
     * 4. Drops tutor_accounts table if it's truly redundant
     */
    public function up(): void
    {
        // STEP 1: Check if timezone in tutor_accounts has any unique purpose
        // Add timezone to work_preferences if needed
        if (!Schema::hasColumn('work_preferences', 'timezone')) {
            Schema::table('work_preferences', function (Blueprint $table) {
                $table->string('timezone', 50)->default('UTC')->after('end_time');
            });
        }

        // STEP 2: Migrate any timezone data from tutor_accounts to work_preferences
        // This assumes tutor has applicant_id relationship
        DB::statement('
            UPDATE work_preferences wp
            INNER JOIN applicants a ON wp.applicant_id = a.applicant_id
            INNER JOIN tutors t ON a.applicant_id = t.applicant_id
            INNER JOIN tutor_accounts ta ON t.tutor_id = ta.tutor_id
            SET wp.timezone = COALESCE(ta.timezone, "UTC")
            WHERE ta.timezone IS NOT NULL
        ');

        // STEP 3: Drop tutor_accounts table as it's redundant
        // All availability data should be in work_preferences (connected to applicant)
        // Account-specific assignments should be in tutor_assignments table
        Schema::dropIfExists('tutor_accounts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tutor_accounts table with proper foreign key
        Schema::create('tutor_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tutor_id'); // Now references tutor.tutor_id (integer)
            $table->unsignedBigInteger('account_id')->nullable();
            $table->json('available_days')->nullable();
            $table->json('available_times')->nullable();
            $table->string('timezone', 255)->default('UTC');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('tutor_id')->references('tutor_id')->on('tutors')->onDelete('cascade');
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['tutor_id', 'account_id']);
        });

        // Remove timezone from work_preferences if we added it
        if (Schema::hasColumn('work_preferences', 'timezone')) {
            Schema::table('work_preferences', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }
    }
};
