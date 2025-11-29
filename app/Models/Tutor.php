<?php

namespace App\Models;
use App\Models\TutorWorkDetail; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class Tutor extends Authenticatable
{
    protected $table = 'tutors';
    protected $primaryKey = 'tutor_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'tutor_id',
        'applicant_id',
        'account_id',
        'tutorID',
        'tusername',
        'email',
        'tpassword',
        'phone_number',
        'sex',
        'date_of_birth',
        'status',
        'hired_date_time',
    ];

    protected $hidden = [
        'tpassword',
        'remember_token',
    ];

    protected $casts = [
        'tpassword' => 'hashed',
    ];

    // Override getAuthPassword to use tpassword field
    public function getAuthPassword()
    {
        return $this->tpassword;
    }

    // Automatically generate formatted ID when creating new tutors
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tutor) {
            if (empty($tutor->tutorID)) {
                $tutor->tutorID = self::generateFormattedId();
            }
        });
    }

    /**
     * Generate formatted ID for new tutors - OGS-T0001 format
     */
    public static function generateFormattedId(): string
    {
        // Get the last tutor by extracting the number from tutorID
        $lastTutor = self::whereNotNull('tutorID')
            ->where('tutorID', 'LIKE', 'OGS-T%')
            ->orderByRaw('CAST(SUBSTRING(tutorID, 6) AS UNSIGNED) DESC')
            ->first();
            
        if ($lastTutor && preg_match('/OGS-T(\d+)/', $lastTutor->tutorID, $matches)) {
            $nextId = ((int) $matches[1]) + 1;
        } else {
            $nextId = 1;
        }
        return 'OGS-T' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate username for tutor - firstnamelastname format
     */
    public static function generateUsername($firstName, $lastName): string
    {
        // Create base username from first and last name
        $baseUsername = strtolower($firstName . $lastName);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        // Check if username already exists and add number if needed
        $username = $baseUsername;
        $counter = 1;
        
        while (self::where('tusername', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Get the applicant that owns this tutor record.
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }

    /**
     * Get the account for this tutor.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    // Relationship to tutor accounts (new multi-account system)
    // tutor_id in tutor_accounts references tutorID (formatted string)
    public function accounts()
    {
        return $this->hasMany(TutorAccount::class, 'tutor_id', 'tutorID');
    }

    // Get account-specific availability
    public function accountAvailability($accountName)
    {
        return $this->accounts()->forAccount($accountName)->active()->first();
    }

    public function assignments()
    {
        return $this->hasMany(TutorAssignment::class, 'tutor_id', 'tutorID');
    }

    // Add the search scope method
    /**
     * Search scope for tutors
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tutorID', 'LIKE', "%{$search}%")
                  ->orWhere('tusername', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('applicant', function($applicantQuery) use ($search) {
                      $applicantQuery->where('first_name', 'LIKE', "%{$search}%")
                                     ->orWhere('last_name', 'LIKE', "%{$search}%")
                                     ->orWhere('email', 'LIKE', "%{$search}%")
                                     ->orWhere('contact_number', 'LIKE', "%{$search}%");
                  });
            });
        }
        return $query;
    }

    // Add the status scope method
    public function scopeStatus($query, $status)
    {
        if (!$status || $status === 'all') {
            return $query;
        }
        
        return $query->where('status', $status);
    }

    // Get formatted available time from tutor accounts (prioritizes GLS account for display)
    public function getFormattedAvailableTimeAttribute()
    {
        // Use the multi-account system - prioritize GLS account for display
        $glsAccount = $this->accountAvailability('GLS');
        if ($glsAccount) {
            return $glsAccount->getFormattedAvailableTimeAttribute();
        }
        
        // Fallback to first available account
        $firstAccount = $this->accounts()->active()->first();
        if ($firstAccount) {
            return $firstAccount->getFormattedAvailableTimeAttribute();
        }
        
        // No fallback needed - return message if no accounts exist
        return 'No availability set';
    }


    // Full name accessor - get from applicant relationship
    public function getFullNameAttribute()
    {
        if ($this->applicant) {
            return trim(($this->applicant->first_name ?? '') . ' ' . ($this->applicant->last_name ?? ''));
        }
        
        // Fallback to username if no applicant
        return $this->tusername ?? 'N/A';
    }

    // Accessors for backward compatibility
    public function getFirstNameAttribute()
    {
        return $this->applicant?->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->applicant?->last_name;
    }

    /**
     * Get the security question for this tutor
     */
    public function securityQuestion()
    {
        return $this->hasOne(SecurityQuestion::class, 'user_id', 'tutorID')
                    ->where('user_type', 'tutor');
    }

    /**
     * Get all security questions for this tutor
     */
    public function securityQuestions()
    {
        return $this->hasMany(SecurityQuestion::class, 'user_id', 'tutorID')
                    ->where('user_type', 'tutor');
    }

    /**
     * Check if tutor has a security question set up
     */
    public function hasSecurityQuestion()
    {
        return $this->securityQuestion()->exists();
    }

    /**
     * Get the payment information for this tutor
     */
    public function paymentInformation()
    {
        return $this->hasOne(EmployeePaymentInformation::class, 'employee_id', 'tutorID')
                    ->where('employee_type', 'tutor');
    }

    /**
     * Get all payment information for this tutor
     */
    public function paymentMethods()
    {
        return $this->hasMany(EmployeePaymentInformation::class, 'employee_id', 'tutorID')
                    ->where('employee_type', 'tutor');
    }

    /**
     * Get the tutor details for this tutor
     */
    public function tutorDetails()
    {
        return $this->hasOne(TutorDetails::class, 'tutor_id', 'tutorID');
    }

    /**
     * Get work details (ph times / durations) for this tutor
     */
    public function workDetails()
    {
        return $this->hasMany(TutorWorkDetail::class, 'tutor_id', 'tutorID');
    }
    /**
     * Get the email address for password reset.
     */
    public function getEmailForPasswordReset()
    {
        return $this->email ?? $this->applicant?->email;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'tutorID';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->tutorID;
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
}
