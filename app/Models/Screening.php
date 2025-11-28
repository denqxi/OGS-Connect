<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screening extends Model
{
    protected $table = 'screening';
    protected $primaryKey = 'screening_id';

    protected $fillable = [
        'applicant_id',
        'supervisor_id',
        'account_id',
        'phase',
        'results',
        'notes',
        'screening_date_time',
    ];

    protected $casts = [
        'screening_date_time' => 'datetime',
    ];

    /**
     * Get the applicant for the screening.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the supervisor for the screening.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id', 'supervisor_id');
    }

    /**
     * Get the account for the screening.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }
}
