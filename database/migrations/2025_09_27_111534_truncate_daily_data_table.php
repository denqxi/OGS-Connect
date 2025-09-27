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
        // Truncate both daily_data and related tutor_assignments tables
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Get counts before truncation
        $dailyDataCount = \Illuminate\Support\Facades\DB::table('daily_data')->count();
        $assignmentCount = \Illuminate\Support\Facades\DB::table('tutor_assignments')->count();
        
        // Truncate both tables
        \Illuminate\Support\Facades\DB::table('tutor_assignments')->truncate();
        \Illuminate\Support\Facades\DB::table('daily_data')->truncate();
        
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "Successfully truncated:\n";
        echo "- {$dailyDataCount} daily_data records\n";
        echo "- {$assignmentCount} tutor_assignment records\n";
        echo "Auto-increment IDs reset to 1.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot rollback a truncate operation
        echo "Cannot rollback table truncation - data has been permanently removed.\n";
    }
};
