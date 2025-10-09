<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    public function statusColor() {
        return match($this->status) {
            'screening' => 'bg-[#65DB7F] text-white',
            'demo' => 'bg-[#FBBF24] text-[#6C5600]',
            'training' => 'bg-[#9DC9FD] text-[#6C5600]',
            'onboarding' => 'bg-[#A78BFA] text-white',
 
        };
    }

    protected $table = 'demos';
    
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
        'main_device',
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
        'demo_schedule',
        'training_schedule',
        'moved_to_demo_at',
        'moved_to_training_at',
        'moved_to_onboarding_at',
        'hired_at',
        'finalized_at',
    ];

    protected $casts = [
        'days' => 'array',
        'platforms' => 'array',
        'can_teach' => 'array',
        'birth_date' => 'date',
        'interview_time' => 'datetime',
        'demo_schedule' => 'datetime',
        'training_schedule' => 'datetime',
        'moved_to_demo_at' => 'datetime',
        'moved_to_training_at' => 'datetime',
        'moved_to_onboarding_at' => 'datetime',
        'hired_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];
}