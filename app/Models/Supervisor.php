<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Supervisor extends Authenticatable
{
    protected $table = 'supervisors';
    protected $primaryKey = 'supervisor_id';
    
    protected $fillable = [
        'supervisor_id',
        'supID',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'email',
        'contact_number',
        'assigned_account',
        'ms_teams',
        'start_time',
        'end_time',
        'days_available',
        'timezone',
        'password',
        'status',
    ];

    protected $casts = [
        'password' => 'hashed',
        'days_available' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
     * Get the route key name for Laravel model binding.
     */
    public function getRouteKeyName()
    {
        return 'supID';
    }

    /**
     * Get the email address for password reset.
     */
    public function getEmailForPasswordReset()
    {
        return $this->email ?? $this->semail;
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
        return $this->supID ?? $this->supervisor_id;
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
        return trim(($this->first_name ?? '') . ' ' . ($this->middle_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    // Accessors for backward compatibility with old field names
    public function getSfnameAttribute()
    {
        return $this->first_name;
    }

    public function getSmnameAttribute()
    {
        return $this->middle_name;
    }

    public function getSlnameAttribute()
    {
        return $this->last_name;
    }

    public function getSemailAttribute()
    {
        return $this->email;
    }

    public function getSconNumAttribute()
    {
        return $this->contact_number;
    }

    public function getSteamsAttribute()
    {
        return $this->ms_teams;
    }

    /**
     * Get formatted available time
     */
    public function getFormattedAvailableTimeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time)->format('g:i A');
            $end = \Carbon\Carbon::parse($this->end_time)->format('g:i A');
            return "{$start} - {$end}";
        }
        return 'Not set';
    }

    /**
     * Get formatted available days
     */
    public function getFormattedAvailableDaysAttribute()
    {
        if ($this->days_available && is_array($this->days_available)) {
            $dayMap = [
                'monday' => 'Mon', 'mon' => 'Mon',
                'tuesday' => 'Tue', 'tue' => 'Tue',
                'wednesday' => 'Wed', 'wed' => 'Wed',
                'thursday' => 'Thu', 'thur' => 'Thu', 'thu' => 'Thu',
                'friday' => 'Fri', 'fri' => 'Fri',
                'saturday' => 'Sat', 'sat' => 'Sat',
                'sunday' => 'Sun', 'sun' => 'Sun'
            ];
            
            $normalizedDays = array_map('strtolower', $this->days_available);
            $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            
            $isAllDays = count(array_intersect($normalizedDays, $allDays)) === 7;
            $isAllWeekdays = count(array_intersect($normalizedDays, $weekdays)) === 5 && count($normalizedDays) === 5;
            
            if ($isAllDays) {
                return 'Mon-Sun';
            } elseif ($isAllWeekdays) {
                return 'Mon-Fri';
            } else {
                $abbrevDays = array_map(function($day) use ($dayMap) {
                    return $dayMap[strtolower($day)] ?? ucfirst(substr($day, 0, 3));
                }, $this->days_available);
                return implode(', ', $abbrevDays);
            }
        }
        return 'Not set';
    }

    /**
     * Search scope for supervisors
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // If searching for formatted ID pattern
                if (preg_match('/^OGS-S(\d+)$/', $search, $matches)) {
                    $q->where('supID', $search);
                } else {
                    // Regular search on other fields
                    $q->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('middle_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('contact_number', 'LIKE', "%{$search}%")
                      ->orWhere('supID', 'LIKE', "%{$search}%");
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
