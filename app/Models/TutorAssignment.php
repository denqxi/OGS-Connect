<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorAssignment extends Model
{
    protected $fillable = [
        'daily_data_id',
        'tutor_id',
        'is_backup',
        'assigned_at',
        'similarity_score',
        'status'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'similarity_score' => 'float',
        'is_backup' => 'boolean'
    ];

    public function dailyData()
    {
        return $this->belongsTo(DailyData::class);
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }
}
