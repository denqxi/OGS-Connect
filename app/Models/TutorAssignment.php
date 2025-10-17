<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorAssignment extends Model
{
    protected $fillable = [
        'daily_data_id',
        'tutor_id',
        'is_backup',
        'was_promoted_from_backup',
        'replaced_tutor_name',
        'promoted_at',
        'assigned_at',
        'similarity_score',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'promoted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'similarity_score' => 'float',
        'is_backup' => 'boolean',
        'was_promoted_from_backup' => 'boolean'
    ];

    public function dailyData()
    {
        return $this->belongsTo(DailyData::class, 'daily_data_id', 'id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }
}
