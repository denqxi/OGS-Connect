<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class Tutor extends Authenticatable
{
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
        'status'
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

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'tutorID', 'tutorID');
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
        
        // Final fallback to old availabilities table if no accounts exist
        $availableSlots = $this->availabilities()
            ->where('availStatus', 'available')
            ->with('timeSlot')
            ->get();

        if ($availableSlots->isEmpty()) {
            return 'No availability set';
        }

        return $availableSlots->map(function($availability) {
            if ($availability->timeSlot) {
                $timeSlot = $availability->timeSlot;
                $day = Carbon::parse($timeSlot->date)->format('D'); // Short day name
                $start = Carbon::parse($timeSlot->startTime)->format('g A');
                $end = Carbon::parse($timeSlot->endTime)->format('g A');
                return $day . ' | ' . $start . ' - ' . $end;
            }
            return 'Invalid time slot';
        })->take(3)->join(', ') . ($availableSlots->count() > 3 ? '...' : '');
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
}
