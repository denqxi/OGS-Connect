<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    public function statusColor() {
        return match($this->status) {
            'pending' => 'bg-[#FBBF24] text-[#6C5600]',
            'rejected' => 'bg-[#F65353] text-white',
            'no_answer' => 'bg-[#FF7515] text-white',
            're_schedule' => 'bg-[#A78BFA] text-white',
            'declined' => 'bg-[#E02F2F] text-[#6C5600]',
            'not_recommended' => 'bg-[#AA1B1B] text-[#6C5600]',
            default => 'bg-gray-500 text-white',
        };
    }
    protected $table = 'applications';
    
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
        'attempt_count',
        'interviewer',
        'notes',
    ];

    protected $casts = [
        'days' => 'array',
        'platforms' => 'array',
        'can_teach' => 'array',
        'birth_date' => 'date',
        'interview_time' => 'datetime',
    ];
}
