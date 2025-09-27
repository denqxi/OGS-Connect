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
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->boolean('is_backup')->default(false)->after('tutor_id');
            $table->dropUnique(['daily_data_id', 'tutor_id']); // Remove the unique constraint
        });
        
        // Add new unique constraint that includes is_backup
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->unique(['daily_data_id', 'tutor_id', 'is_backup']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->dropUnique(['daily_data_id', 'tutor_id', 'is_backup']);
            $table->dropColumn('is_backup');
        });
        
        // Restore original unique constraint
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->unique(['daily_data_id', 'tutor_id']);
        });
    }
};
