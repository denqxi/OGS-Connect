<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $table = 'applications';
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'applicant_id',
        'attempt_count',
        'status',
        'interviewer',
        'notes',
        'term_agreement',
        'application_date_time',
    ];

    protected $casts = [
        'term_agreement' => 'boolean',
        'application_date_time' => 'datetime',
    ];

    /**
     * Get the applicant that owns the application.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    public function statusColor() {
        return match($this->status) {
            'pending' => 'bg-[#FBBF24] text-[#6C5600]',
            'rejected' => 'bg-[#F65353] text-white',
            'no_answer' => 'bg-[#FF7515] text-white',
            're_schedule' => 'bg-[#A78BFA] text-white',
            'declined' => 'bg-[#E02F2F] text-[#6C5600]',
            'not_recommended' => 'bg-[#AA1B1B] text-[#6C5600]',
            default => 'bg-gray-500 text-white',
        };
    }

    // Accessors for backward compatibility - these access related data
    // Note: Controllers should eager load relationships to avoid N+1 queries
    public function getFirstNameAttribute()
    {
        return $this->applicant?->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->applicant?->last_name;
    }

    public function getBirthDateAttribute()
    {
        return $this->applicant?->birth_date;
    }

    public function getAddressAttribute()
    {
        return $this->applicant?->address;
    }

    public function getContactNumberAttribute()
    {
        return $this->applicant?->contact_number;
    }

    public function getEmailAttribute()
    {
        return $this->applicant?->email;
    }

    public function getMsTeamsAttribute()
    {
        return $this->applicant?->ms_teams;
    }

    public function getInterviewTimeAttribute()
    {
        return $this->applicant?->interview_time;
    }

    public function getEducationAttribute()
    {
        return $this->applicant?->qualification?->education;
    }

    public function getEslExperienceAttribute()
    {
        return $this->applicant?->qualification?->esl_experience;
    }

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

    public function getSourceAttribute()
    {
        return $this->applicant?->referral?->source;
    }

    public function getReferrerNameAttribute()
    {
        return $this->applicant?->referral?->referrer_name;
    }

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

    public function getTermsAgreementAttribute()
    {
        return $this->term_agreement ?? false;
    }
}
