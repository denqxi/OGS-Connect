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
        Schema::table('tutors', function (Blueprint $table) {
            // JSON field to store available days (e.g., ["monday", "tuesday", "friday"])
            $table->json('available_days')->nullable()->after('status');
            
            // JSON field to store available time ranges (e.g., ["06:00-12:00", "18:00-22:00"])
            $table->json('available_times')->nullable()->after('available_days');
            
            // General preferred time range category
            $table->enum('preferred_time_range', ['morning', 'afternoon', 'evening', 'flexible'])->default('flexible')->after('available_times');
            
            // Timezone preference (default to PHT)
            $table->string('timezone', 10)->default('PHT')->after('preferred_time_range');
            
            // Additional notes for availability
            $table->text('availability_notes')->nullable()->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn([
                'available_days',
                'available_times', 
                'preferred_time_range',
                'timezone',
                'availability_notes'
            ]);
        });
    }
};
