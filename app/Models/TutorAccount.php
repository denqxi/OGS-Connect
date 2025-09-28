<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Services\AccountTimeSlotConfig;

class TutorAccount extends Model
{
    protected $fillable = [
        'tutor_id',
        'account_name',
        'username',        // Added username field
        'screen_name',     // Added screen_name field
        'available_days',
        'available_times', 
        'preferred_time_range',
        'timezone',
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

        $dayLabels = [
            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'
        ];

        $dayTimeStrings = [];

        foreach ($this->available_days as $day) {
            $dayKey = strtolower($day);
            $dayLabel = $dayLabels[$dayKey] ?? ucfirst($day);
            
            // Try both the original day case and lowercase to handle different data formats
            $dayTimes = $this->available_times[$day] ?? $this->available_times[$dayKey] ?? [];

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
        if (!$this->available_days || !in_array($day, $this->available_days)) {
            return false;
        }

        $dayTimes = $this->available_times[$day] ?? [];
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

        if (!$this->available_times) {
            return []; // No time slots to validate
        }

        return AccountTimeSlotConfig::validateTimeSlots($this->account_name, $this->available_times);
    }

    /**
     * Validate available days for this account according to account-specific rules
     */
    public function validateAvailableDays(): array
    {
        if (!$this->account_name) {
            return ['Account name is required for validation'];
        }

        if (!$this->available_days) {
            return []; // No days to validate
        }

        return AccountTimeSlotConfig::validateAvailableDays($this->account_name, $this->available_days);
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
