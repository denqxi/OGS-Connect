<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This consolidated migration creates all application tables in one go.
     * It replaces 60+ individual migrations for better performance.
     */
    public function up(): void
    {
        // APPLICANTS TABLE
        if (!Schema::hasTable('applicants')) {
            Schema::create('applicants', function (Blueprint $table) {
                $table->id('applicant_id');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('contact_number');
                $table->text('address');
                $table->date('birth_date');
                $table->string('gender');
                $table->timestamps();
            });
        }

        // QUALIFICATIONS TABLE
        if (!Schema::hasTable('qualifications')) {
            Schema::create('qualifications', function (Blueprint $table) {
                $table->id('qualification_id');
                $table->unsignedBigInteger('applicant_id');
                $table->string('degree_level');
                $table->string('major');
                $table->string('institution');
                $table->year('graduation_year');
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // REQUIREMENTS TABLE
        if (!Schema::hasTable('requirements')) {
            Schema::create('requirements', function (Blueprint $table) {
                $table->id('requirement_id');
                $table->unsignedBigInteger('applicant_id');
                $table->string('resume_path');
                $table->string('valid_id_path');
                $table->string('diploma_path')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // REFERRALS TABLE
        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id('referral_id');
                $table->unsignedBigInteger('applicant_id');
                $table->string('referrer_name')->nullable();
                $table->string('relationship')->nullable();
                $table->string('referrer_contact')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // APPLICATIONS TABLE
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->id('application_id');
                $table->unsignedBigInteger('applicant_id');
                $table->string('position_applied');
                $table->enum('status', ['pending', 'screening', 'passed', 'failed', 'onboarding'])->default('pending');
                $table->date('application_date');
                $table->text('interviewer_notes')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // SUPERVISORS TABLE
        if (!Schema::hasTable('supervisors')) {
            Schema::create('supervisors', function (Blueprint $table) {
                $table->id('supervisor_id');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('contact_number')->nullable();
                $table->text('address')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('gender')->nullable();
                $table->json('work_preferences')->nullable();
                $table->json('payment_info')->nullable();
                $table->string('profile_photo')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // ACCOUNTS TABLE
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id('account_id');
                $table->string('account_name');
                $table->timestamps();
            });
        }

        // SCREENING TABLE
        if (!Schema::hasTable('screenings')) {
            Schema::create('screenings', function (Blueprint $table) {
                $table->id('screening_id');
                $table->unsignedBigInteger('applicant_id');
                $table->date('screening_date');
                $table->enum('result', ['passed', 'failed', 'pending'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // ARCHIVES TABLE
        if (!Schema::hasTable('archives')) {
            Schema::create('archives', function (Blueprint $table) {
                $table->id('archive_id');
                $table->unsignedBigInteger('applicant_id');
                $table->string('reason');
                $table->date('archive_date');
                $table->text('details')->nullable();
                $table->enum('type', ['application', 'screening', 'demo', 'onboarding'])->default('application');
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // WORK PREFERENCES TABLE
        if (!Schema::hasTable('work_preferences')) {
            Schema::create('work_preferences', function (Blueprint $table) {
                $table->id('preference_id');
                $table->unsignedBigInteger('applicant_id');
                $table->json('available_days');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('preferred_grade_level')->nullable();
                $table->string('preferred_subjects')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // ONBOARDINGS TABLE
        if (!Schema::hasTable('onboardings')) {
            Schema::create('onboardings', function (Blueprint $table) {
                $table->id('onboarding_id');
                $table->unsignedBigInteger('applicant_id');
                $table->date('demo_date')->nullable();
                $table->time('demo_time')->nullable();
                $table->enum('demo_result', ['passed', 'failed', 'pending', 'scheduled'])->default('scheduled');
                $table->enum('onboarding_status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
                $table->text('demo_notes')->nullable();
                $table->date('training_start_date')->nullable();
                $table->date('training_end_date')->nullable();
                $table->string('trainer_name')->nullable();
                $table->text('training_notes')->nullable();
                $table->unsignedBigInteger('assessed_by')->nullable();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // TUTORS TABLE
        if (!Schema::hasTable('tutors')) {
            Schema::create('tutors', function (Blueprint $table) {
                $table->id('tutor_id');
                $table->unsignedBigInteger('applicant_id');
                $table->unsignedBigInteger('account_id')->nullable();
                $table->string('username')->unique();
                $table->string('password');
                $table->enum('employment_status', ['active', 'inactive', 'suspended'])->default('active');
                $table->date('hire_date');
                $table->json('work_preferences')->nullable();
                $table->string('profile_photo')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
                $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('set null');
            });
        }

        // SCHEDULES DAILY DATA TABLE
        if (!Schema::hasTable('schedules_daily_data')) {
            Schema::create('schedules_daily_data', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('day');
                $table->time('time');
                $table->string('duration');
                $table->string('school');
                $table->string('class');
                $table->timestamps();
            });
        }

        // ASSIGNED DAILY DATA TABLE
        if (!Schema::hasTable('assigned_daily_data')) {
            Schema::create('assigned_daily_data', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_daily_data_id');
                $table->unsignedBigInteger('main_tutor')->nullable();
                $table->unsignedBigInteger('backup_tutor')->nullable();
                $table->unsignedBigInteger('assigned_supervisor')->nullable();
                $table->enum('class_status', ['not_assigned', 'partially_assigned', 'fully_assigned', 'pending_acceptance', 'cancelled'])->default('not_assigned');
                $table->text('notes')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->unsignedBigInteger('finalized_by')->nullable();
                $table->timestamps();
                $table->foreign('schedule_daily_data_id')->references('id')->on('schedules_daily_data')->onDelete('cascade');
                $table->foreign('main_tutor')->references('tutor_id')->on('tutors')->onDelete('set null');
                $table->foreign('backup_tutor')->references('tutor_id')->on('tutors')->onDelete('set null');
                $table->foreign('assigned_supervisor')->references('supervisor_id')->on('supervisors')->onDelete('set null');
            });
        }

        // SCHEDULE HISTORY TABLE
        if (!Schema::hasTable('schedule_history')) {
            Schema::create('schedule_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_daily_data_id');
                $table->string('action');
                $table->text('details')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->timestamps();
                $table->foreign('schedule_daily_data_id')->references('id')->on('schedules_daily_data')->onDelete('cascade');
            });
        }

        // SCHEDULE CANCELLATIONS TABLE
        if (!Schema::hasTable('schedule_cancellations')) {
            Schema::create('schedule_cancellations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_daily_data_id');
                $table->text('reason');
                $table->unsignedBigInteger('cancelled_by');
                $table->string('cancelled_by_type');
                $table->timestamp('cancelled_at');
                $table->timestamps();
                $table->foreign('schedule_daily_data_id')->references('id')->on('schedules_daily_data')->onDelete('cascade');
            });
        }

        // ARCHIVED APPLICATIONS TABLE
        if (!Schema::hasTable('archived_applications')) {
            Schema::create('archived_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('applicant_id');
                $table->unsignedBigInteger('application_id');
                $table->string('position_applied');
                $table->string('status');
                $table->date('application_date');
                $table->text('reason')->nullable();
                $table->date('archived_date');
                $table->timestamps();
                $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');
            });
        }

        // NOTIFICATIONS TABLE
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->string('icon')->nullable();
                $table->string('color')->default('blue');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'user_type', 'is_read']);
            });
        }

        // TUTOR WORK DETAILS TABLE
        if (!Schema::hasTable('tutor_work_details')) {
            Schema::create('tutor_work_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tutor_id');
                $table->unsignedBigInteger('schedule_daily_data_id')->nullable();
                $table->unsignedBigInteger('assignment_id')->nullable();
                $table->date('date');
                $table->string('day')->nullable();
                $table->time('start_time');
                $table->time('end_time');
                $table->decimal('hours_worked', 5, 2);
                $table->decimal('rate_per_hour', 8, 2);
                $table->decimal('total_amount', 10, 2);
                $table->string('school')->nullable();
                $table->string('class')->nullable();
                $table->string('screenshot')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('tutor_id')->references('tutor_id')->on('tutors')->onDelete('cascade');
                $table->foreign('schedule_daily_data_id')->references('id')->on('schedules_daily_data')->onDelete('set null');
                $table->foreign('assignment_id')->references('id')->on('assigned_daily_data')->onDelete('set null');
            });
        }

        // TUTOR WORK DETAIL APPROVALS TABLE
        if (!Schema::hasTable('tutor_work_detail_approvals')) {
            Schema::create('tutor_work_detail_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('work_detail_id');
                $table->unsignedBigInteger('supervisor_id')->nullable();
                $table->enum('status', ['approved', 'rejected']);
                $table->text('note')->nullable();
                $table->timestamp('approved_at');
                $table->timestamps();
                $table->foreign('work_detail_id')->references('id')->on('tutor_work_details')->onDelete('cascade');
                $table->foreign('supervisor_id')->references('supervisor_id')->on('supervisors')->onDelete('set null');
            });
        }

        // PAYROLL HISTORY TABLE
        if (!Schema::hasTable('payroll_history')) {
            Schema::create('payroll_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tutor_id');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_hours', 8, 2);
                $table->decimal('total_amount', 10, 2);
                $table->enum('status', ['pending', 'approved', 'paid', 'finalized'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('tutor_id')->references('tutor_id')->on('tutors')->onDelete('cascade');
            });
        }

        // PAYROLL FINALIZATIONS TABLE
        if (!Schema::hasTable('payroll_finalizations')) {
            Schema::create('payroll_finalizations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_history_id');
                $table->unsignedBigInteger('finalized_by');
                $table->timestamp('finalized_at');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->foreign('payroll_history_id')->references('id')->on('payroll_history')->onDelete('cascade');
                $table->foreign('finalized_by')->references('supervisor_id')->on('supervisors')->onDelete('cascade');
            });
        }

        // SECURITY QUESTIONS TABLE
        if (!Schema::hasTable('security_questions')) {
            Schema::create('security_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->string('question');
                $table->string('answer_hash');
                $table->timestamps();
                $table->unique(['user_id', 'user_type']);
            });
        }

        // EMPLOYEE PAYMENT INFORMATION TABLE
        if (!Schema::hasTable('employee_payment_information')) {
            Schema::create('employee_payment_information', function (Blueprint $table) {
                $table->id();
                $table->string('employee_type');
                $table->unsignedBigInteger('employee_id');
                $table->string('payment_method');
                $table->json('payment_details');
                $table->timestamps();
                $table->unique(['employee_type', 'employee_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payment_information');
        Schema::dropIfExists('security_questions');
        Schema::dropIfExists('payroll_finalizations');
        Schema::dropIfExists('payroll_history');
        Schema::dropIfExists('tutor_work_detail_approvals');
        Schema::dropIfExists('tutor_work_details');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('archived_applications');
        Schema::dropIfExists('schedule_cancellations');
        Schema::dropIfExists('schedule_history');
        Schema::dropIfExists('assigned_daily_data');
        Schema::dropIfExists('schedules_daily_data');
        Schema::dropIfExists('tutors');
        Schema::dropIfExists('onboardings');
        Schema::dropIfExists('work_preferences');
        Schema::dropIfExists('archives');
        Schema::dropIfExists('screenings');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('supervisors');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('applicants');
    }
};
