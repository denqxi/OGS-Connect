<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_name',
        'description',
        'industry',
    ];

    /**
     * Get the screenings for the account.
     */
    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class, 'account_id', 'account_id');
    }

    /**
     * Get the onboardings for the account.
     */
    public function onboardings(): HasMany
    {
        return $this->hasMany(Onboarding::class, 'account_id', 'account_id');
    }

    /**
     * Get the tutors for the account.
     */
    public function tutors(): HasMany
    {
        return $this->hasMany(Tutor::class, 'account_id', 'account_id');
    }

    /**
     * Get the tutor accounts (availability records) for this company
     */
    public function tutorAccounts(): HasMany
    {
        return $this->hasMany(TutorAccount::class, 'account_id', 'account_id');
    }
}
