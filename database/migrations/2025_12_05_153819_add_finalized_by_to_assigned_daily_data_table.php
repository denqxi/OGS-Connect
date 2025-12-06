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
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            // Add finalized_by column only if it doesn't exist
            if (!Schema::hasColumn('assigned_daily_data', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('finalized_at');
            }
            
            // Remove cancelled_at column if it exists
            if (Schema::hasColumn('assigned_daily_data', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            // Drop finalized_by column
            $table->dropColumn('finalized_by');
            
            // Re-add cancelled_at column if it was removed
            $table->timestamp('cancelled_at')->nullable();
        });
    }
};
