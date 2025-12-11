<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleCancellation extends Model
{
    protected $fillable = [
        'assignment_id',
        'schedule_id',
        'original_main_tutor',
        'backup_tutor_activated',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_by_id',
        'cancelled_at',
    ];

    protected $casts = [
        'backup_tutor_activated' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssignedDailyData::class, 'assignment_id');
    }

    public function schedule()
    {
        return $this->belongsTo(ScheduleDailyData::class, 'schedule_id');
    }

    public function originalMainTutor()
    {
        return $this->belongsTo(Tutor::class, 'original_main_tutor', 'tutorID');
    }
}
