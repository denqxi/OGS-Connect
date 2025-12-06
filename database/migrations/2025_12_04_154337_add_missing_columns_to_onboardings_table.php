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
        Schema::table('onboardings', function (Blueprint $table) {
            // Add assessed_by column if it doesn't exist
            if (!Schema::hasColumn('onboardings', 'assessed_by')) {
                $table->unsignedBigInteger('assessed_by')->nullable()->after('account_id');
                $table->foreign('assessed_by')->references('supervisor_id')->on('supervisors')->onDelete('cascade');
            }
            
            // Add onboarding_date_time column if it doesn't exist
            if (!Schema::hasColumn('onboardings', 'onboarding_date_time')) {
                $table->datetime('onboarding_date_time')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboardings', function (Blueprint $table) {
            // Check if assessed_by column exists before dropping
            if (Schema::hasColumn('onboardings', 'assessed_by')) {
                // Check if the foreign key exists before dropping
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'onboardings' AND COLUMN_NAME = 'assessed_by' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = DATABASE()");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['assessed_by']);
                }
                $table->dropColumn('assessed_by');
            }
            
            if (Schema::hasColumn('onboardings', 'onboarding_date_time')) {
                $table->dropColumn('onboarding_date_time');
            }
        });
    }
};
