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
            // Remove legacy availability fields since they're replaced by tutor_accounts table
            $table->dropColumn([
                'available_days',
                'available_times', 
                'preferred_time_range',
                'timezone',
                'availability_notes'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            // Restore the fields in case of rollback
            $table->json('available_days')->nullable()->after('status');
            $table->json('available_times')->nullable()->after('available_days');
            $table->string('preferred_time_range')->nullable()->after('available_times');
            $table->string('timezone')->default('UTC')->after('preferred_time_range');
            $table->text('availability_notes')->nullable()->after('timezone');
        });
    }
};
