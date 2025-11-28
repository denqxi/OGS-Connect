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
}
