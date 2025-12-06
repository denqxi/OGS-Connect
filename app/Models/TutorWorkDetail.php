<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorWorkDetail extends Model
{
    protected $table = 'tutor_work_details';

    protected $fillable = [
        'tutor_id',
        'work_type',
        'day',
        'start_time',
        'end_time',
        'duration_minutes',
        'rate_per_hour',
        'rate_per_class',
        'screenshot',
        'status',
        'note'
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }

    public function approvals()
    {
        return $this->hasMany(TutorWorkDetailApproval::class, 'work_detail_id');
    }
}
