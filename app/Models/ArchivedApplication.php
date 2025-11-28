<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedApplication extends Model
{
    protected $table = 'archived_applications';
    
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'address',
        'contact_number',
        'email',
        'ms_teams',
        'education',
        'esl_experience',
        'resume_link',
        'intro_video',
        'work_type',
        'speedtest',
        'backup_device',
        'source',
        'referrer_name',
        'start_time',
        'end_time',
        'days',
        'platforms',
        'can_teach',
        'interview_time',
            'status',
        'assigned_account',
        'interviewer',
        'notes',
        'attempt_count',
        'archived_at',
    ];

    protected $casts = [
        'days' => 'array',
        'platforms' => 'array',
        'can_teach' => 'array',
        'birth_date' => 'date',
        'interview_time' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
