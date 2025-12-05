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
            if (Schema::hasColumn('onboardings', 'assessed_by')) {
                $table->dropForeign(['assessed_by']);
                $table->dropColumn('assessed_by');
            }
            
            if (Schema::hasColumn('onboardings', 'onboarding_date_time')) {
                $table->dropColumn('onboarding_date_time');
            }
        });
    }
};
