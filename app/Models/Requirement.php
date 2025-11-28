<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    protected $table = 'requirement';
    protected $primaryKey = 'applicant_requirement_id';

    protected $fillable = [
        'applicant_id',
        'resume_link',
        'intro_video',
        'work_type',
        'speedtest',
        'main_devices',
        'backup_devices',
    ];

    /**
     * Get the applicant that owns the requirement.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }
}

