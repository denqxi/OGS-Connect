<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $table = 'referrals';
    protected $primaryKey = 'applicant_referral_id';

    protected $fillable = [
        'applicant_id',
        'source',
        'referrer_name',
    ];

    /**
     * Get the applicant that owns the referral.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }
}

