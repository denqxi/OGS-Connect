<?php

namespace App\Models;
use App\Models\TutorWorkDetail;
use App\Models\EmployeePaymentInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

/**
 * @property int $tutor_id
 * @property int $applicant_id
 * @property int $account_id
 * @property string $tutorID
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string $full_name (accessor)
 * @property string $first_name (accessor)
 * @property string $last_name (accessor)
 * @property string $phone_number (accessor)
 * @property string $date_of_birth (accessor)
 * @property string $formatted_available_time (accessor)
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Applicant $applicant
 * @property-read Account $account
 * @property-read WorkPreference $workPreferences
 */
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
        'username',
        'email',
        'password',
        'status',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $appends = [
        'full_name',
        'first_name',
        'last_name',
        'phone_number',
        'date_of_birth',
        'assigned_account',
        'start_time',
        'end_time',
        'days_available',
    ];

    // Override getAuthPassword to use password field
    public function getAuthPassword()
    {
        return $this->password;
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
        
        while (self::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Generate company email for tutor - username@ogsconnect.com format
     */
    public static function generateCompanyEmail($username): string
    {
        return strtolower($username) . '@ogsconnect.com';
    }
    
    /**
     * Check if username is available
     */
    public static function isUsernameAvailable($username): bool
    {
        return !self::where('username', $username)->exists();
    }
    
    /**
     * Check if email is available
     */
    public static function isEmailAvailable($email): bool
    {
        return !self::where('email', $email)->exists();
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

    // Relationship to work preferences through applicant
    public function workPreferences()
    {
        return $this->hasOneThrough(
            WorkPreference::class,
            Applicant::class,
            'applicant_id', // Foreign key on applicants table
            'applicant_id', // Foreign key on work_preferences table
            'applicant_id', // Local key on tutor table
            'applicant_id'  // Local key on applicants table
        );
    }

    // Alias for backward compatibility
    public function availability()
    {
        return $this->workPreferences();
    }

    public function assignments()
    {
        return $this->hasMany(TutorAssignment::class, 'tutor_id', 'tutorID');
    }

    public function payrollHistory()
    {
        return $this->hasMany(PayrollHistory::class, 'tutor_id', 'tutor_id');
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
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
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

    // Get formatted available time from work preferences
    public function getFormattedAvailableTimeAttribute()
    {
        $workPref = $this->workPreferences;
        
        if (!$workPref || !$workPref->days_available) {
            return 'No availability set';
        }
        
        // Handle both array and JSON string for days_available
        if (is_array($workPref->days_available)) {
            $days = $workPref->days_available;
        } elseif (is_string($workPref->days_available)) {
            $days = json_decode($workPref->days_available, true);
        } else {
            $days = [];
        }
        
        if (empty($days)) {
            return 'No availability set';
        }
        
        $startTime = $workPref->start_time ? date('g:i A', strtotime($workPref->start_time)) : 'N/A';
        $endTime = $workPref->end_time ? date('g:i A', strtotime($workPref->end_time)) : 'N/A';
        
        return implode(', ', $days) . ': ' . $startTime . ' - ' . $endTime;
    }


    // Assigned account accessor - get account name from account relationship
    public function getAssignedAccountAttribute()
    {
        return $this->account ? $this->account->account_name : null;
    }

    // Start time accessor - get from work preferences
    public function getStartTimeAttribute()
    {
        return $this->workPreferences ? $this->workPreferences->start_time : null;
    }

    // End time accessor - get from work preferences
    public function getEndTimeAttribute()
    {
        return $this->workPreferences ? $this->workPreferences->end_time : null;
    }

    // Days available accessor - get from work preferences
    public function getDaysAvailableAttribute()
    {
        if (!$this->workPreferences || !$this->workPreferences->days_available) {
            return null;
        }
        
        // Handle both array and JSON string
        if (is_array($this->workPreferences->days_available)) {
            return $this->workPreferences->days_available;
        } elseif (is_string($this->workPreferences->days_available)) {
            return json_decode($this->workPreferences->days_available, true);
        }
        
        return null;
    }

    // Full name accessor - get from applicant relationship
    public function getFullNameAttribute()
    {
        if ($this->applicant) {
            return trim(($this->applicant->first_name ?? '') . ' ' . ($this->applicant->last_name ?? ''));
        }
        
        // Fallback to username if no applicant
        return $this->username ?? 'N/A';
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
        return $this->hasMany(SecurityQuestion::class, 'user_id', 'tutor_id')
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
     * TODO: Uncomment when employee_payment_information table exists
     */
    // public function paymentInformation()
    // {
    //     return $this->hasOne(EmployeePaymentInformation::class, 'employee_id', 'tutorID')
    //                 ->where('employee_type', 'tutor');
    // }

    /**
     * Get all payment information for this tutor
     */
    public function paymentMethods()
    {
        return $this->hasMany(EmployeePaymentInformation::class, 'employee_id', 'tutorID')
                    ->where('employee_type', 'tutor');
    }

    /**
     * Get the tutor details from applicant relationship
     * Tutor details are stored in the applicants table
     */
    public function tutorDetails()
    {
        // Return applicant as tutor details since all details are stored there
        return $this->applicant();
    }
    
    /**
     * Get address from applicant
     */
    public function getAddressAttribute()
    {
        return $this->applicant?->address;
    }
    
    /**
     * Get educational attainment from applicant's qualification
     */
    public function getEducationalAttainmentAttribute()
    {
        return $this->applicant?->qualification?->education;
    }
    
    /**
     * Get ESL teaching experience from applicant's qualification
     */
    public function getEslTeachingExperienceAttribute()
    {
        return $this->applicant?->qualification?->esl_experience;
    }
    
    /**
     * Get work setup from applicant's requirement
     */
    public function getWorkSetupAttribute()
    {
        return $this->applicant?->requirement?->work_type;
    }
    
    /**
     * Get first day of teaching (hire date)
     */
    public function getFirstDayOfTeachingAttribute()
    {
        return $this->hire_date_time;
    }
    
    /**
     * Get MS Teams ID from applicant
     */
    public function getMsTeamsIdAttribute()
    {
        return $this->applicant?->ms_teams ?? null;
    }

    /**
     * Get phone number from applicant
     */
    public function getPhoneNumberAttribute()
    {
        return $this->applicant?->contact_number ?? null;
    }

    /**
     * Get date of birth from applicant
     */
    public function getDateOfBirthAttribute()
    {
        return $this->applicant?->birth_date ?? null;
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
     * Get the route key name for Laravel model binding.
     */
    public function getRouteKeyName()
    {
        return 'tutorID';
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
        return null; // Remember token column removed
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        // Remember token column removed - do nothing
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return null; // Remember token column removed
    }
}
