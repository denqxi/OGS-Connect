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
        // Drop foreign key constraints for the misspelled columns
        DB::statement('ALTER TABLE onboardings DROP FOREIGN KEY onboardings_asessed_by_foreign');
        
        // Drop the misspelled columns
        Schema::table('onboardings', function (Blueprint $table) {
            if (Schema::hasColumn('onboardings', 'asessed_by')) {
                $table->dropColumn('asessed_by');
            }
            if (Schema::hasColumn('onboardings', 'onbaording_date_time')) {
                $table->dropColumn('onbaording_date_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboardings', function (Blueprint $table) {
            // Re-add the columns if needed (though they're typos, we recreate for reversibility)
            if (!Schema::hasColumn('onboardings', 'asessed_by')) {
                $table->unsignedBigInteger('asessed_by')->nullable();
                $table->foreign('asessed_by')->references('supervisor_id')->on('supervisors')->onDelete('cascade');
            }
            if (!Schema::hasColumn('onboardings', 'onbaording_date_time')) {
                $table->datetime('onbaording_date_time')->nullable();
            }
        });
    }
};
