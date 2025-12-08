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
        Schema::table('tutor_work_details', function (Blueprint $table) {
            // Add schedule_daily_data_id to link directly to the schedule
            if (!Schema::hasColumn('tutor_work_details', 'schedule_daily_data_id')) {
                $table->unsignedBigInteger('schedule_daily_data_id')->nullable()->after('assignment_id');
                
                // Add foreign key
                $table->foreign('schedule_daily_data_id')
                    ->references('id')
                    ->on('schedules_daily_data')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_work_details', function (Blueprint $table) {
            if (Schema::hasColumn('tutor_work_details', 'schedule_daily_data_id')) {
                $table->dropForeign(['schedule_daily_data_id']);
                $table->dropColumn('schedule_daily_data_id');
            }
        });
    }
};
