<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schedule_cancellations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('schedule_id');
            $table->string('original_main_tutor'); // tutor_id format
            $table->boolean('backup_tutor_activated')->default(false);
            $table->text('cancellation_reason');
            $table->enum('cancelled_by', ['main_tutor', 'supervisor']);
            $table->string('cancelled_by_id');
            $table->timestamp('cancelled_at');
            $table->timestamps();

            $table->foreign('assignment_id')->references('id')->on('assigned_daily_data')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules_daily_data')->onDelete('cascade');
        });

        // Add cancellation fields to assigned_daily_data table
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            $table->boolean('is_cancelled')->default(false)->after('class_status');
            $table->unsignedBigInteger('cancellation_id')->nullable()->after('is_cancelled');
            
            $table->foreign('cancellation_id')->references('id')->on('schedule_cancellations')->onDelete('set null');
        });

        // Add payment_blocked flag to tutor_work_details for cancelled schedules
        Schema::table('tutor_work_details', function (Blueprint $table) {
            $table->boolean('payment_blocked')->default(false)->after('status');
            $table->string('block_reason')->nullable()->after('payment_blocked');
        });
    }

    public function down()
    {
        Schema::table('tutor_work_details', function (Blueprint $table) {
            $table->dropColumn(['payment_blocked', 'block_reason']);
        });

        Schema::table('assigned_daily_data', function (Blueprint $table) {
            $table->dropForeign(['cancellation_id']);
            $table->dropColumn(['is_cancelled', 'cancellation_id']);
        });

        Schema::dropIfExists('schedule_cancellations');
    }
};
