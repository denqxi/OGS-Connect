<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    protected $primaryKey = 'applicant_id';
    protected $table = 'applicants';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'address',
        'contact_number',
        'email',
        'ms_teams',
        'interview_time',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'interview_time' => 'datetime',
    ];

    /**
     * Get the qualification for the applicant.
     */
    public function qualification(): HasOne
    {
        return $this->hasOne(Qualification::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the requirement for the applicant.
     */
    public function requirement(): HasOne
    {
        return $this->hasOne(Requirement::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the referral for the applicant.
     */
    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the work preference for the applicant.
     */
    public function workPreference(): HasOne
    {
        return $this->hasOne(WorkPreference::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the applications for the applicant.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the screenings for the applicant.
     */
    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the onboardings for the applicant.
     */
    public function onboardings(): HasMany
    {
        return $this->hasMany(Onboarding::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the tutors for the applicant.
     */
    public function tutors(): HasMany
    {
        return $this->hasMany(Tutor::class, 'applicant_id', 'applicant_id');
    }

    // Accessor methods for tutor details compatibility
    /**
     * Get educational attainment from qualification
     */
    public function getEducationalAttainmentAttribute()
    {
        if (isset($this->attributes['educational_attainment'])) {
            return $this->attributes['educational_attainment'];
        }
        return $this->qualification?->education;
    }

    /**
     * Get ESL teaching experience from qualification
     */
    public function getEslTeachingExperienceAttribute()
    {
        if (isset($this->attributes['esl_teaching_experience'])) {
            return $this->attributes['esl_teaching_experience'];
        }
        return $this->qualification?->esl_experience;
    }

    /**
     * Get work setup from requirement
     */
    public function getWorkSetupAttribute()
    {
        if (isset($this->attributes['work_setup'])) {
            return $this->attributes['work_setup'];
        }
        return $this->requirement?->work_type;
    }

    /**
     * Get MS Teams ID (mapped from ms_teams field)
     */
    public function getMsTeamsIdAttribute()
    {
        if (isset($this->attributes['ms_teams_id'])) {
            return $this->attributes['ms_teams_id'];
        }
        return $this->ms_teams;
    }

    /**
     * Get first day of teaching from related tutor
     */
    public function getFirstDayOfTeachingAttribute()
    {
        if (isset($this->attributes['first_day_of_teaching'])) {
            return $this->attributes['first_day_of_teaching'];
        }
        return $this->tutors()->first()?->hire_date_time;
    }
}
