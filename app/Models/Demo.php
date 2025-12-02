<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    // This model now acts as a compatibility/proxy layer for the new `screening` table.
    // Many parts of the codebase still reference `Demo` and expect denormalized fields.
    // To avoid changing all call sites, we map common attributes to related models.

    protected $table = 'screening';
    protected $primaryKey = 'screening_id';

    // Allow filling the screening fields that exist in the new table (used by new flows)
    protected $fillable = [
        'applicant_id',
        'supervisor_id',
        'account_id',
        'phase',
        'results',
        'notes',
        'screening_date_time',
    ];

    protected $casts = [
        'screening_date_time' => 'datetime',
    ];

    // Relationships to underlying normalized tables
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    // Provide the `status` property expected by older code (maps to `phase`)
    public function getStatusAttribute()
    {
        return $this->attributes['phase'] ?? null;
    }

    // Provide the `id` property that returns the primary key (screening_id)
    public function getIdAttribute()
    {
        return $this->attributes['screening_id'] ?? null;
    }

    // Map demo-specific accessors to data coming from related models
    public function getFirstNameAttribute()
    {
        return $this->applicant?->first_name;
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

    public function getAssignedAccountAttribute()
    {
        return $this->account?->account_name;
    }

    public function getDemoScheduleAttribute()
    {
        return $this->screening_date_time;
    }

    public function getScheduledAtAttribute()
    {
        return $this->screening_date_time;
    }

    public function getInterviewTimeAttribute()
    {
        return $this->applicant?->interview_time;
    }

    // Work preference and requirement / qualification related accessors
    public function getStartTimeAttribute()
    {
        return $this->applicant?->workPreference?->start_time;
    }

    public function getEndTimeAttribute()
    {
        return $this->applicant?->workPreference?->end_time;
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

    public function getAddressAttribute()
    {
        return $this->applicant?->address;
    }

    public function getEslExperienceAttribute()
    {
        return $this->applicant?->qualification?->esl_experience;
    }

    public function getEducationAttribute()
    {
        return $this->applicant?->qualification?->education;
    }

    public function getResumeLinkAttribute()
    {
        return $this->applicant?->requirement?->resume_link;
    }

    public function getIntroVideoAttribute()
    {
        return $this->applicant?->requirement?->intro_video;
    }

    public function getMainDeviceAttribute()
    {
        return $this->applicant?->requirement?->main_devices;
    }

    public function getBackupDeviceAttribute()
    {
        return $this->applicant?->requirement?->backup_devices;
    }

    public function getSourceAttribute()
    {
        return $this->applicant?->referral?->source;
    }

    public function getReferrerNameAttribute()
    {
        return $this->applicant?->referral?->referrer_name;
    }

    public function getWorkTypeAttribute()
    {
        return $this->applicant?->requirement?->work_type;
    }

    public function getSpeedtestAttribute()
    {
        return $this->applicant?->requirement?->speedtest;
    }

    public function statusColor()
    {
        return match($this->status) {
            'screening' => 'bg-[#65DB7F] text-white',
            'demo' => 'bg-[#FBBF24] text-[#6C5600]',
            'training' => 'bg-[#9DC9FD] text-[#6C5600]',
            'onboarding' => 'bg-[#A78BFA] text-white',
            'pending' => 'bg-[#FBBF24] text-[#6C5600]',
            'passed' => 'bg-[#65DB7F] text-white',
            'failed' => 'bg-[#F65353] text-white',
            'rejected' => 'bg-[#F65353] text-white',
            'no_answer' => 'bg-[#FF7515] text-white',
            're_schedule' => 'bg-[#A78BFA] text-white',
            'declined' => 'bg-[#E02F2F] text-white',
            'not_recommended' => 'bg-[#AA1B1B] text-white',
            default => 'bg-gray-200 text-black',
        };
    }
}