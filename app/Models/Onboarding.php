<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Onboarding extends Model
{
    protected $table = 'onboardings';
    protected $primaryKey = 'onboarding_id';

    protected $fillable = [
        'applicant_id',
        'account_id',
        'assessed_by',
        'phase',
        'notes',
        'onboarding_date_time',
    ];

    protected $casts = [
        'onboarding_date_time' => 'datetime',
    ];

    /**
     * Get the applicant for the onboarding.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the account for the onboarding.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    /**
     * Get the supervisor who assessed the onboarding.
     */
    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'assessed_by', 'supervisor_id');
    }
}
