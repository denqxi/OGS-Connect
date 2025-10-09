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
        Schema::create('demos', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->text('address');
            $table->string('contact_number');
            $table->string('email');
            $table->text('ms_teams')->nullable();
            $table->string('education');
            $table->string('esl_experience');
            $table->text('resume_link');
            $table->text('intro_video');
            $table->string('work_type');
            $table->text('speedtest')->nullable();
            $table->text('main_device')->nullable();
            $table->text('backup_device')->nullable();
            $table->string('source');
            $table->string('referrer_name')->nullable();
            $table->string('start_time');
            $table->string('end_time');
            $table->json('days');
            $table->json('platforms');
            $table->json('can_teach');
            $table->dateTime('interview_time');
            $table->string('status');
            $table->string('assigned_account')->nullable();
            $table->string('interviewer')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('demo_schedule')->nullable();
            $table->timestamp('moved_to_demo_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demos');
    }
};