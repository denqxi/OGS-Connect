<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Tutor extends Authenticatable
{
    use Notifiable;
    protected $primaryKey = 'tutorID';
    public $incrementing = false; // Primary key is not auto-incrementing
    protected $keyType = 'string'; // Primary key is string type
    
    protected $fillable = [
        'tutorID',
        'tusername',
        'first_name',
        'last_name',
        'email',
        'tpassword',
        'phone_number',
        'sex',
        'date_of_birth',
        'status'
    ];

    protected $hidden = [
        'tpassword',
        'remember_token',
    ];

    protected $casts = [
        'tpassword' => 'hashed',
    ];

    /**
     * Get the email address for notifications.
     */
    public function getEmailForNotifications()
    {
        return trim(str_replace(["\r", "\n"], '', $this->email));
    }

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
                $tutor->tutorID = $tutor->generateFormattedId();
            }
        });
    }

    /**
     * Generate formatted ID for new tutors
     */
    public function generateFormattedId(): string
    {
        // Get the last tutor by extracting the number from tutorID
        $lastTutor = self::orderByRaw('CAST(SUBSTRING(tutorID, 6) AS UNSIGNED) DESC')->first();
        if ($lastTutor && preg_match('/OGS-T(\d+)/', $lastTutor->tutorID, $matches)) {
            $nextId = ((int) $matches[1]) + 1;
        } else {
            $nextId = 1;
        }
        return 'OGS-T' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }


    // Relationship to tutor accounts (new multi-account system)
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
                $q->where('tutorID', 'LIKE', "%{$search}%") // tutorID now contains formatted ID
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
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


    // Full name accessor
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        
        // Fallback to username if no full name
        return $this->tusername ?? 'N/A';
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
}
