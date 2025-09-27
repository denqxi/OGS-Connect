<?php
// app/Models/TutorAvailability.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorAvailability extends Model
{
    protected $fillable = [
        'tutor_id',
        'day',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'is_available' => 'boolean'
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }
}
