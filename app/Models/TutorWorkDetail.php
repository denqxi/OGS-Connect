<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorWorkDetail extends Model
{
    protected $table = 'tutor_work_details';

    protected $fillable = [
        'tutor_id',
        'assignment_id',
        'schedule_daily_data_id',
        'work_type',
        'day',
        'start_time',
        'end_time',
        'duration_minutes',
        'rate_per_hour',
        'rate_per_class',
        'screenshot',
        'proof_image',
        'status',
        'note',
        'payment_blocked',
        'block_reason'
    ];

    protected $casts = [
        'payment_blocked' => 'boolean',
    ];

    // Surface helpful derived values for display and calculations
    protected $appends = [
        'duration_hours',
        'computed_amount',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }

    public function assignment()
    {
        return $this->belongsTo(AssignedDailyData::class, 'assignment_id');
    }

    public function schedule()
    {
        return $this->belongsTo(ScheduleDailyData::class, 'schedule_daily_data_id');
    }

    public function approvals()
    {
        return $this->hasMany(TutorWorkDetailApproval::class, 'work_detail_id');
    }

    /**
     * Duration in hours, rounded to 2 decimals.
     */
    public function getDurationHoursAttribute(): float
    {
        $minutes = abs($this->duration_minutes ?? 0);
        return round($minutes / 60, 2);
    }

    /**
     * Computed amount for this work detail based on work type and rate.
     */
    public function getComputedAmountAttribute(): float
    {
        if (($this->work_type ?? '') === 'hourly') {
            return round(($this->rate_per_hour ?? 0) * $this->duration_hours, 2);
        }

        return round($this->rate_per_class ?? 0, 2);
    }
}
