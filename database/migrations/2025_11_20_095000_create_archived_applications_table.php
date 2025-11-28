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
        Schema::create('archived_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('ms_teams')->nullable();
            $table->string('education')->nullable();
            $table->text('esl_experience')->nullable();
            $table->string('resume_link')->nullable();
            $table->string('intro_video')->nullable();
            $table->string('work_type')->nullable();
            $table->string('speedtest')->nullable();
            $table->string('backup_device')->nullable();
            $table->string('source')->nullable();
            $table->string('referrer_name')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days')->nullable();
            $table->json('platforms')->nullable();
            $table->json('can_teach')->nullable();
            $table->timestamp('interview_time')->nullable();
            $table->enum('final_status', ['recommended','not_recommended','pending'])->default('pending');
            $table->string('assigned_account')->nullable();
            $table->string('interviewer')->nullable();
            $table->text('notes')->nullable();
            $table->integer('attempt_count')->default(0);
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_applications');
    }
};
