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
        // Update existing 'active' status to 'not_assigned' for records with no tutors
        DB::statement("
            UPDATE assigned_daily_data 
            SET class_status = 'not_assigned' 
            WHERE class_status = 'active' 
            AND main_tutor IS NULL 
            AND backup_tutor IS NULL
        ");
        
        // Update existing 'active' status to 'partially_assigned' for records with only one tutor
        DB::statement("
            UPDATE assigned_daily_data 
            SET class_status = 'partially_assigned' 
            WHERE class_status = 'active' 
            AND (
                (main_tutor IS NOT NULL AND backup_tutor IS NULL) 
                OR (main_tutor IS NULL AND backup_tutor IS NOT NULL)
            )
        ");
        
        // Update existing 'active' status to 'fully_assigned' for records with both tutors
        DB::statement("
            UPDATE assigned_daily_data 
            SET class_status = 'fully_assigned' 
            WHERE class_status = 'active' 
            AND main_tutor IS NOT NULL 
            AND backup_tutor IS NOT NULL
        ");
        
        // Alter the enum column to include new values
        DB::statement("
            ALTER TABLE assigned_daily_data 
            MODIFY COLUMN class_status ENUM('not_assigned', 'partially_assigned', 'fully_assigned', 'cancelled') 
            DEFAULT 'not_assigned'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all assignment statuses back to 'active'
        DB::statement("
            UPDATE assigned_daily_data 
            SET class_status = 'active' 
            WHERE class_status IN ('not_assigned', 'partially_assigned', 'fully_assigned')
        ");
        
        // Revert the enum column to original values
        DB::statement("
            ALTER TABLE assigned_daily_data 
            MODIFY COLUMN class_status ENUM('active', 'cancelled') 
            DEFAULT 'active'
        ");
    }
};
