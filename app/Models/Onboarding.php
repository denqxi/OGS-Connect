<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Onboarding extends Model
{
    protected $table = 'onboardings';
    protected $primaryKey = 'onboarding_id';

    protected $fillable = [
        'applicant_id',
        'account_id',
        'assessed_by',
        'phase',
        'notes',
        'onboarding_date_time',
    ];

    protected $casts = [
        'onboarding_date_time' => 'datetime',
    ];

    /**
     * Get the applicant for the onboarding.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the account for the onboarding.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    /**
     * Get the supervisor who assessed the onboarding.
     */
    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'assessed_by', 'supervisor_id');
    }

    // Accessors for compatibility with views expecting Demo model structure
    public function getIdAttribute()
    {
        return $this->attributes['onboarding_id'] ?? null;
    }

    public function getFirstNameAttribute()
    {
        return $this->applicant?->first_name;
    }

    public function getMiddleNameAttribute()
    {
        return $this->applicant?->middle_name;
    }

    public function getLastNameAttribute()
    {
        return $this->applicant?->last_name;
    }

    public function getContactNumberAttribute()
    {
        return $this->applicant?->contact_number;
    }

    public function getEmailAttribute()
    {
        return $this->applicant?->email;
    }

    public function getBirthDateAttribute()
    {
        return $this->applicant?->birth_date;
    }

    public function getAddressAttribute()
    {
        return $this->applicant?->address;
    }

    public function getMsTeamsAttribute()
    {
        return $this->applicant?->ms_teams;
    }

    public function getEducationAttribute()
    {
        return $this->applicant?->qualification?->education;
    }

    public function getEslExperienceAttribute()
    {
        return $this->applicant?->qualification?->esl_experience;
    }

    public function getAssignedAccountAttribute()
    {
        return $this->account?->account_name;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['phase'] ?? null;
    }

    public function getInterviewTimeAttribute()
    {
        return $this->applicant?->interview_time;
    }

    public function getDemoScheduleAttribute()
    {
        return $this->onboarding_date_time;
    }

    public function getScheduledAtAttribute()
    {
        return $this->onboarding_date_time;
    }

    public function getNotesAttribute()
    {
        return $this->attributes['notes'] ?? null;
    }

    // Accessors for Requirement fields
    public function getResumeLinkAttribute()
    {
        return $this->applicant?->requirement?->resume_link;
    }

    public function getIntroVideoAttribute()
    {
        return $this->applicant?->requirement?->intro_video;
    }

    public function getWorkTypeAttribute()
    {
        return $this->applicant?->requirement?->work_type;
    }

    public function getSpeedtestAttribute()
    {
        return $this->applicant?->requirement?->speedtest;
    }

    public function getMainDeviceAttribute()
    {
        return $this->applicant?->requirement?->main_devices;
    }

    public function getBackupDeviceAttribute()
    {
        return $this->applicant?->requirement?->backup_devices;
    }

    // Accessors for Referral fields
    public function getSourceAttribute()
    {
        return $this->applicant?->referral?->source;
    }

    public function getReferrerNameAttribute()
    {
        return $this->applicant?->referral?->referrer_name;
    }

    // Accessors for WorkPreference fields
    public function getStartTimeAttribute()
    {
        return $this->applicant?->workPreference?->start_time;
    }

    public function getEndTimeAttribute()
    {
        return $this->applicant?->workPreference?->end_time;
    }

    public function getWorkingDaysAttribute()
    {
        return $this->applicant?->workPreference?->working_days;
    }

    public function getWorkingHoursAttribute()
    {
        return $this->applicant?->workPreference?->working_hours;
    }

    // Accessor for Qualification
    public function getQualificationAttribute()
    {
        return $this->applicant?->qualification;
    }

    public function getRequirementAttribute()
    {
        return $this->applicant?->requirement;
    }

    public function getReferralAttribute()
    {
        return $this->applicant?->referral;
    }

    public function getWorkPreferenceAttribute()
    {
        return $this->applicant?->workPreference;
    }

    public function getDaysAttribute()
    {
        return $this->applicant?->workPreference?->days_available;
    }

    public function getPlatformsAttribute()
    {
        return $this->applicant?->workPreference?->platform;
    }

    public function getCanTeachAttribute()
    {
        return $this->applicant?->workPreference?->can_teach;
    }
}
