<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Qualification extends Model
{
    protected $table = 'qualifications';
    protected $primaryKey = 'applicant_qualification_id';

    protected $fillable = [
        'applicant_id',
        'education',
        'esl_experience',
    ];

    /**
     * Get the applicant that owns the qualification.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }
}

