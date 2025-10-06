<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Services\AccountTimeSlotConfig;

class TutorAccount extends Model
{
    // Accessor for glsID (maps to gls_id DB field) - only for camelCase access
    public function getGlsIDAttribute()
    {
        return $this->attributes['gls_id'] ?? '';
    }

    // Accessor for glsUsername (maps to username DB field)
    public function getGlsUsernameAttribute()
    {
        return $this->username ?? '';
    }

    // Accessor for glsScreenName (maps to screen_name DB field)
    public function getGlsScreenNameAttribute()
    {
        return $this->screen_name ?? '';
    }
    protected $fillable = [
        'tutor_id',
        'account_name',
        'gls_id',          // GLS numeric ID
        'account_number',  // Account number for all account types
        'username',        // Added username field
        'screen_name',     // Added screen_name field
        'available_days',
        'available_times', 
        'preferred_time_range',
        'timezone',
        'restricted_start_time',
        'restricted_end_time',
        'company_notes',
        'availability_notes',
        'status'
    ];

    protected $casts = [
        'available_days' => 'array',
        'available_times' => 'array'
    ];

    // Relationship back to tutor
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutorID');
    }

    // Get formatted availability for this account
    public function getFormattedAvailableTimeAttribute()
    {
        if (!$this->available_days || !$this->available_times) {
            return 'No availability set';
        }

        // Handle case where available_days might be a string instead of array
        $availableDays = $this->available_days;
        if (is_string($availableDays)) {
            $availableDays = json_decode($availableDays, true) ?? [];
        }
        
        // Handle case where available_times might be a string instead of array
        $availableTimes = $this->available_times;
        if (is_string($availableTimes)) {
            $availableTimes = json_decode($availableTimes, true) ?? [];
        }

        if (empty($availableDays) || empty($availableTimes)) {
            return 'No availability set';
        }

        // Sort available_days in chronological order
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $availableDays = array_values(array_intersect($dayOrder, $availableDays));

        $dayLabels = [
            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'
        ];

        $dayTimeStrings = [];

        foreach ($availableDays as $day) {
            $dayKey = strtolower($day);
            $dayLabel = $dayLabels[$dayKey] ?? ucfirst($day);
            
            // Try both the original day case and lowercase to handle different data formats
            $dayTimes = $availableTimes[$day] ?? $availableTimes[$dayKey] ?? [];

            if (!empty($dayTimes)) {
                // Take only the first time slot for this day
                $firstTimeSlot = $dayTimes[0] ?? '';
                
                if (strpos($firstTimeSlot, '-') !== false) {
                    [$start, $end] = explode('-', $firstTimeSlot);
                    try {
                        $startFormatted = Carbon::createFromFormat('H:i', trim($start))->format('H:i');
                        $endFormatted = Carbon::createFromFormat('H:i', trim($end))->format('H:i');
                        $formattedTime = $startFormatted . '-' . $endFormatted;
                    } catch (\Exception $e) {
                        $formattedTime = $firstTimeSlot;
                    }
                } else {
                    $formattedTime = $firstTimeSlot;
                }
                
                $dayTimeStrings[] = $dayLabel . ': ' . $formattedTime;
            } else {
                $dayTimeStrings[] = $dayLabel . ': No times set';
            }
        }

        return implode(', ', $dayTimeStrings);
    }

    // Check if tutor is available on specific day and time for this account
    public function isAvailableAt($day, $time)
    {
        // Handle case where available_days might be a string instead of array
        $availableDays = $this->available_days;
        if (is_string($availableDays)) {
            $availableDays = json_decode($availableDays, true) ?? [];
        }
        
        if (!$availableDays || !in_array($day, $availableDays)) {
            return false;
        }

        // Handle case where available_times might be a string instead of array
        $availableTimes = $this->available_times;
        if (is_string($availableTimes)) {
            $availableTimes = json_decode($availableTimes, true) ?? [];
        }

        $dayTimes = $availableTimes[$day] ?? [];
        $timeMinutes = Carbon::parse($time)->hour * 60 + Carbon::parse($time)->minute;

        foreach ($dayTimes as $timeRange) {
            if (strpos($timeRange, '-') !== false) {
                [$start, $end] = explode('-', $timeRange);
                $startMinutes = Carbon::createFromFormat('H:i', trim($start))->hour * 60 + Carbon::createFromFormat('H:i', trim($start))->minute;
                $endMinutes = Carbon::createFromFormat('H:i', trim($end))->hour * 60 + Carbon::createFromFormat('H:i', trim($end))->minute;
                
                if ($timeMinutes >= $startMinutes && $timeMinutes <= $endMinutes) {
                    return true;
                }
            }
        }

        return false;
    }

    // Scope to filter by account
    public function scopeForAccount($query, $accountName)
    {
        return $query->where('account_name', $accountName);
    }

    // Scope to get active accounts
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Validate time slots for this account according to account-specific rules
     */
    public function validateTimeSlots(): array
    {
        if (!$this->account_name) {
            return ['Account name is required for validation'];
        }

        // Handle case where available_times might be a string instead of array
        $availableTimes = $this->available_times;
        if (is_string($availableTimes)) {
            $availableTimes = json_decode($availableTimes, true) ?? [];
        }

        if (!$availableTimes) {
            return []; // No time slots to validate
        }

        return AccountTimeSlotConfig::validateTimeSlots($this->account_name, $availableTimes);
    }

    /**
     * Validate available days for this account according to account-specific rules
     */
    public function validateAvailableDays(): array
    {
        if (!$this->account_name) {
            return ['Account name is required for validation'];
        }

        // Handle case where available_days might be a string instead of array
        $availableDays = $this->available_days;
        if (is_string($availableDays)) {
            $availableDays = json_decode($availableDays, true) ?? [];
        }

        if (!$availableDays) {
            return []; // No days to validate
        }

        return AccountTimeSlotConfig::validateAvailableDays($this->account_name, $availableDays);
    }

    /**
     * Get all validation errors for this tutor account
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        
        $dayErrors = $this->validateAvailableDays();
        $timeErrors = $this->validateTimeSlots();
        
        return array_merge($dayErrors, $timeErrors);
    }

    /**
     * Check if this tutor account has valid configuration
     */
    public function isValidConfiguration(): bool
    {
        return empty($this->getValidationErrors());
    }

    /**
     * Get allowed time slots for this account
     */
    public function getAllowedTimeSlots(): ?array
    {
        return AccountTimeSlotConfig::getAllowedTimeSlots($this->account_name);
    }

    /**
     * Get allowed days for this account
     */
    public function getAllowedDays(): ?array
    {
        return AccountTimeSlotConfig::getAllowedDays($this->account_name);
    }

    /**
     * Check if this account allows flexible time slots
     */
    public function isFlexibleAccount(): bool
    {
        return AccountTimeSlotConfig::isFlexibleAccount($this->account_name);
    }

    /**
     * Check if this account only allows predefined time slots
     */
    public function isPredefinedAccount(): bool
    {
        return AccountTimeSlotConfig::isPredefinedAccount($this->account_name);
    }

    /**
     * Check if this account is open (no time restrictions)
     */
    public function isOpenAccount(): bool
    {
        return AccountTimeSlotConfig::isOpenAccount($this->account_name);
    }

    /**
     * Check if a time is within the company's restricted hours
     */
    public function isTimeWithinRestrictions(string $time): bool
    {
        // If no restrictions, allow any time
        if (!$this->restricted_start_time || !$this->restricted_end_time) {
            return true;
        }

        $timeCarbon = Carbon::createFromFormat('H:i:s', $time);
        $startTime = Carbon::createFromFormat('H:i:s', $this->restricted_start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $this->restricted_end_time);

        return $timeCarbon->between($startTime, $endTime);
    }

    /**
     * Get company time restrictions as a readable string
     */
    public function getTimeRestrictionsString(): string
    {
        if (!$this->restricted_start_time || !$this->restricted_end_time) {
            return 'Open hours (no restrictions)';
        }

        $start = Carbon::createFromFormat('H:i:s', $this->restricted_start_time)->format('g:i A');
        $end = Carbon::createFromFormat('H:i:s', $this->restricted_end_time)->format('g:i A');
        
        return "{$start} - {$end}";
    }

    /**
     * Get account configuration info for display
     */
    public function getAccountInfo(): array
    {
        $rules = AccountTimeSlotConfig::getRules($this->account_name);
        
        if (!$rules) {
            return [
                'type' => 'unknown',
                'description' => 'Account not configured',
                'allowed_days' => [],
                'allowed_slots' => null
            ];
        }

        return [
            'type' => $rules['type'],
            'description' => $rules['description'],
            'allowed_days' => $rules['allowed_days'],
            'allowed_slots' => $rules['allowed_slots'] ?? null,
            'restrictions' => $this->getRestrictionsDescription()
        ];
    }

    /**
     * Get human-readable description of account restrictions
     */
    public function getRestrictionsDescription(): string
    {
        $rules = AccountTimeSlotConfig::getRules($this->account_name);
        
        if (!$rules) {
            return 'No restrictions configured';
        }

        if ($rules['type'] === 'flexible') {
            $dayList = implode(', ', array_map('ucfirst', $rules['allowed_days']));
            $timeRange = $rules['min_time'] . ' - ' . $rules['max_time'];
            return "Flexible time slots allowed on {$dayList} between {$timeRange}";
        } elseif ($rules['type'] === 'predefined') {
            $dayList = implode(', ', array_map('ucfirst', $rules['allowed_days']));
            $slotList = implode(', ', $rules['allowed_slots']);
            return "Only specific time slots allowed on {$dayList}: {$slotList}";
        } elseif ($rules['type'] === 'open') {
            return "Open time - any time slots allowed on any day";
        }

        return 'Unknown restriction type';
    }
}
