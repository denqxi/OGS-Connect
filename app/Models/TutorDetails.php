<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TutorDetails extends Model
{
    protected $table = 'tutor_details';
    
    protected $fillable = [
        'tutor_id',
        'address',
        'ms_teams_id',
        'esl_experience',
        'work_setup',
        'first_day_teaching',
        'educational_attainment',
        'additional_notes'
    ];

    protected $casts = [
        'first_day_teaching' => 'date',
    ];

    /**
     * Get the tutor that owns the details.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }

    /**
     * Get work setup options
     */
    public static function getWorkSetupOptions()
    {
        return [
            'WFH' => 'Work From Home',
            'WAS' => 'Work At Site',
            'Hybrid' => 'Hybrid'
        ];
    }

    /**
     * Get educational attainment options
     */
    public static function getEducationalAttainmentOptions()
    {
        return [
            'High School' => 'High School',
            'Associate Degree' => 'Associate Degree',
            'Bachelors Degree' => 'Bachelor\'s Degree',
            'Masters Degree' => 'Master\'s Degree',
            'Doctorate' => 'Doctorate',
            'Other' => 'Other'
        ];
    }

    /**
     * Get formatted work setup
     */
    public function getFormattedWorkSetupAttribute()
    {
        return self::getWorkSetupOptions()[$this->work_setup] ?? $this->work_setup;
    }

    /**
     * Get formatted educational attainment
     */
    public function getFormattedEducationalAttainmentAttribute()
    {
        return self::getEducationalAttainmentOptions()[$this->educational_attainment] ?? $this->educational_attainment;
    }

    /**
     * Get formatted first day of teaching
     */
    public function getFormattedFirstDayTeachingAttribute()
    {
        return $this->first_day_teaching ? $this->first_day_teaching->format('M j, Y') : null;
    }
}