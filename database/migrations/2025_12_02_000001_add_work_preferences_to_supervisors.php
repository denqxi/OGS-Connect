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
        Schema::table('supervisor', function (Blueprint $table) {
            // Remove shift column
            $table->dropColumn('shift');
            
            // Add work preference columns similar to tutors
            $table->time('start_time')->nullable()->after('ms_teams');
            $table->time('end_time')->nullable()->after('start_time');
            $table->json('days_available')->nullable()->after('end_time');
            $table->string('timezone', 50)->default('UTC')->after('days_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisor', function (Blueprint $table) {
            // Restore shift column
            $table->string('shift', 75)->nullable()->after('ms_teams');
            
            // Remove work preference columns
            $table->dropColumn(['start_time', 'end_time', 'days_available', 'timezone']);
        });
    }
};
