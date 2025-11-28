<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPreference extends Model
{
    protected $table = 'work_preferences';
    protected $primaryKey = 'applicant_preference_id';

    protected $fillable = [
        'applicant_id',
        'start_time',
        'end_time',
        'days_available',
        'platform',
        'can_teach',
    ];

    protected $casts = [
        'days_available' => 'array',
        'platform' => 'array',
        'can_teach' => 'array',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the applicant that owns the work preference.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }
}

