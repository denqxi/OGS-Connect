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
        // First, remove duplicate entries (keep the most recent one per tutor + period)
        $duplicates = DB::select('
            SELECT tutor_id, pay_period, COUNT(*) as count 
            FROM payroll_history 
            GROUP BY tutor_id, pay_period 
            HAVING count > 1
        ');

        foreach ($duplicates as $duplicate) {
            // Get all IDs for this tutor+period, ordered by created_at desc
            $ids = DB::table('payroll_history')
                ->where('tutor_id', $duplicate->tutor_id)
                ->where('pay_period', $duplicate->pay_period)
                ->orderBy('created_at', 'desc')
                ->pluck('payroll_history_id')
                ->toArray();

            // Keep the first (most recent), delete the rest
            $idsToDelete = array_slice($ids, 1);
            
            if (!empty($idsToDelete)) {
                DB::table('payroll_history')
                    ->whereIn('payroll_history_id', $idsToDelete)
                    ->delete();
                    
                echo "Removed " . count($idsToDelete) . " duplicate(s) for tutor_id={$duplicate->tutor_id}, pay_period={$duplicate->pay_period}\n";
            }
        }

        // Now add unique constraint to prevent future duplicates
        Schema::table('payroll_history', function (Blueprint $table) {
            $table->unique(['tutor_id', 'pay_period'], 'unique_tutor_pay_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_history', function (Blueprint $table) {
            $table->dropUnique('unique_tutor_pay_period');
        });
    }
};
