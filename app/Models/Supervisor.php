<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Supervisor extends Authenticatable
{
    protected $primaryKey = 'supID';
    public $incrementing = false; // Primary key is not auto-incrementing
    protected $keyType = 'string'; // Primary key is string type
    
    protected $fillable = [
        'supID',
        'sfname',
        'smname',
        'slname',
        'birth_date',
        'semail',
        'sconNum',
        'password',
        'assigned_account',
        'srole',
        'saddress',
        'steams',
        'sshift',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Automatically generate formatted ID when creating new supervisors
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supervisor) {
            if (empty($supervisor->supID)) {
                $supervisor->supID = $supervisor->generateFormattedId();
            }
        });
    }

    /**
     * Generate formatted ID for new supervisors
     */
    public function generateFormattedId(): string
    {
        // Get the last supervisor by extracting the number from supID
        $lastSupervisor = self::orderByRaw('CAST(SUBSTRING(supID, 6) AS UNSIGNED) DESC')->first();
        if ($lastSupervisor && preg_match('/OGS-S(\d+)/', $lastSupervisor->supID, $matches)) {
            $nextId = ((int) $matches[1]) + 1;
        } else {
            $nextId = 1;
        }
        return 'OGS-S' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the email address for password reset.
     */
    public function getEmailForPasswordReset()
    {
        return $this->semail;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'supID';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }




    /**
     * Get the full name of the supervisor
     */
    public function getFullNameAttribute()
    {
        return trim(($this->sfname ?? '') . ' ' . ($this->smname ?? '') . ' ' . ($this->slname ?? ''));
    }

    /**
     * Search scope for supervisors
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // If searching for formatted ID pattern, extract numeric part
                if (preg_match('/^OGS-S(\d+)$/', $search, $matches)) {
                    $numericId = (int) $matches[1];
                    $q->where('supID', $numericId);
                } else {
                    // Regular search on other fields
                    $q->where('sfname', 'LIKE', "%{$search}%")
                      ->orWhere('smname', 'LIKE', "%{$search}%")
                      ->orWhere('slname', 'LIKE', "%{$search}%")
                      ->orWhere('semail', 'LIKE', "%{$search}%");
                }
            });
        }
        return $query;
    }

    /**
     * Get the security question for this supervisor
     */
    public function securityQuestion()
    {
        return $this->hasOne(SecurityQuestion::class, 'user_id', 'supID')
                    ->where('user_type', 'supervisor');
    }

    /**
     * Get all security questions for this supervisor
     */
    public function securityQuestions()
    {
        return $this->hasMany(SecurityQuestion::class, 'user_id', 'supID')
                    ->where('user_type', 'supervisor');
    }

    /**
     * Check if supervisor has a security question set up
     */
    public function hasSecurityQuestion()
    {
        return $this->securityQuestion()->exists();
    }

    /**
     * Get the payment information for this supervisor
     */
    public function paymentInformation()
    {
        return $this->hasOne(EmployeePaymentInformation::class, 'employee_id', 'supID')
                    ->where('employee_type', 'supervisor');
    }

    /**
     * Get tutors assigned to this supervisor's account
     */
    public function assignedTutors()
    {
        if (!$this->assigned_account) {
            return collect(); // Return empty collection if no account assigned
        }
        
        return Tutor::whereHas('accounts', function($query) {
            $query->where('account_name', $this->assigned_account);
        })->get();
    }

    /**
     * Get the account name with proper formatting
     */
    public function getAssignedAccountNameAttribute()
    {
        return $this->assigned_account ? $this->assigned_account . ' Supervisor' : 'Unassigned';
    }

    /**
     * Scope to filter supervisors by assigned account
     */
    public function scopeForAccount($query, $accountName)
    {
        return $query->where('assigned_account', $accountName);
    }
}
