<?php

namespace App\Services;

class AccountTimeSlotConfig
{
    /**
     * Account-specific time slot restrictions
     * 
     * Format:
     * - 'type' => 'flexible' means any time range is allowed
     * - 'type' => 'predefined' means only specific slots are allowed
     * - 'allowed_slots' => array of allowed time slots in 'HH:MM-HH:MM' format
     */
    protected static array $accountRules = [
        'GLS' => [
            'type' => 'flexible',
            'description' => 'GLS allows flexible time ranges from 7:00 AM to 3:00 PM, weekdays only',
            'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'min_time' => '07:00',
            'max_time' => '15:00',
            'allowed_slots' => null // flexible means any time within range
        ],
        
        'BABILALA' => [
            'type' => 'predefined',
            'description' => 'Babilala restricts to 8:00 PM to 10:00 PM time slot only',
            'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'allowed_slots' => [
                '20:00-22:00'  // 8 PM to 10 PM only
            ]
        ],
        
        'TUTLO' => [
            'type' => 'open',
            'description' => 'Tutlo allows open time - any time slots on any day',
            'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'min_time' => '00:00',
            'max_time' => '23:59',
            'allowed_slots' => null // open means any time is allowed
        ],
        
        'TALK195' => [
            'type' => 'open',
            'description' => 'Talk195 allows open time - any time slots on any day',
            'allowed_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'min_time' => '00:00',
            'max_time' => '23:59',
            'allowed_slots' => null // open means any time is allowed
        ]
    ];

    /**
     * Get rules for a specific account
     */
    public static function getRules(string $accountName): ?array
    {
        return static::$accountRules[strtoupper($accountName)] ?? null;
    }

    /**
     * Get all account rules
     */
    public static function getAllRules(): array
    {
        return static::$accountRules;
    }

    /**
     * Check if an account exists in the configuration
     */
    public static function accountExists(string $accountName): bool
    {
        return isset(static::$accountRules[strtoupper($accountName)]);
    }

    /**
     * Validate time slots for a specific account
     */
    public static function validateTimeSlots(string $accountName, array $timeSlots): array
    {
        $rules = static::getRules($accountName);
        $errors = [];
        
        if (!$rules) {
            $errors[] = "Account '{$accountName}' is not configured";
            return $errors;
        }

        if ($rules['type'] === 'flexible') {
            // For flexible accounts (like GLS), validate time ranges are within limits
            foreach ($timeSlots as $day => $slots) {
                $dayLower = strtolower($day);
                if (!in_array($dayLower, $rules['allowed_days'])) {
                    $errors[] = "Day '{$day}' is not allowed for {$accountName} account";
                    continue;
                }

                foreach ($slots as $slot) {
                    if (!static::isValidTimeRange($slot)) {
                        $errors[] = "Invalid time format: '{$slot}' for day {$day}";
                        continue;
                    }

                    if (!static::isWithinTimeRange($slot, $rules['min_time'], $rules['max_time'])) {
                        $errors[] = "Time slot '{$slot}' is outside allowed hours ({$rules['min_time']}-{$rules['max_time']}) for {$accountName}";
                    }
                }
            }
        } elseif ($rules['type'] === 'predefined') {
            // For predefined accounts (like Babilala), validate against allowed slots
            foreach ($timeSlots as $day => $slots) {
                $dayLower = strtolower($day);
                if (!in_array($dayLower, $rules['allowed_days'])) {
                    $errors[] = "Day '{$day}' is not allowed for {$accountName} account";
                    continue;
                }

                foreach ($slots as $slot) {
                    if (!in_array($slot, $rules['allowed_slots'])) {
                        $allowedSlotsStr = implode(', ', $rules['allowed_slots']);
                        $errors[] = "Time slot '{$slot}' is not allowed for {$accountName}. Allowed slots: {$allowedSlotsStr}";
                    }
                }
            }
        } elseif ($rules['type'] === 'open') {
            // For open accounts (like Tutlo, Talk195), only validate basic time format
            foreach ($timeSlots as $day => $slots) {
                $dayLower = strtolower($day);
                if (!in_array($dayLower, $rules['allowed_days'])) {
                    $errors[] = "Day '{$day}' is not allowed for {$accountName} account";
                    continue;
                }

                foreach ($slots as $slot) {
                    if (!static::isValidTimeRange($slot)) {
                        $errors[] = "Invalid time format: '{$slot}' for day {$day}";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate available days for a specific account
     */
    public static function validateAvailableDays(string $accountName, array $days): array
    {
        $rules = static::getRules($accountName);
        $errors = [];

        if (!$rules) {
            $errors[] = "Account '{$accountName}' is not configured";
            return $errors;
        }

        foreach ($days as $day) {
            $dayLower = strtolower($day);
            if (!in_array($dayLower, $rules['allowed_days'])) {
                $errors[] = "Day '{$day}' is not allowed for {$accountName} account";
            }
        }

        return $errors;
    }

    /**
     * Get allowed time slots for an account
     */
    public static function getAllowedTimeSlots(string $accountName): ?array
    {
        $rules = static::getRules($accountName);
        return $rules ? $rules['allowed_slots'] : null;
    }

    /**
     * Get allowed days for an account
     */
    public static function getAllowedDays(string $accountName): ?array
    {
        $rules = static::getRules($accountName);
        return $rules ? $rules['allowed_days'] : null;
    }

    /**
     * Check if account type is flexible or predefined
     */
    public static function isFlexibleAccount(string $accountName): bool
    {
        $rules = static::getRules($accountName);
        return $rules && $rules['type'] === 'flexible';
    }

    /**
     * Check if account type is predefined slots only
     */
    public static function isPredefinedAccount(string $accountName): bool
    {
        $rules = static::getRules($accountName);
        return $rules && $rules['type'] === 'predefined';
    }

    /**
     * Check if account type is open (no restrictions)
     */
    public static function isOpenAccount(string $accountName): bool
    {
        $rules = static::getRules($accountName);
        return $rules && $rules['type'] === 'open';
    }

    /**
     * Validate time range format (HH:MM-HH:MM)
     */
    private static function isValidTimeRange(string $timeRange): bool
    {
        if (strpos($timeRange, '-') === false) {
            return false;
        }

        [$start, $end] = explode('-', $timeRange, 2);
        $start = trim($start);
        $end = trim($end);

        // Validate time format HH:MM
        return preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $start) && 
               preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $end);
    }

    /**
     * Check if time range is within allowed min/max times
     */
    private static function isWithinTimeRange(string $timeRange, string $minTime, string $maxTime): bool
    {
        [$start, $end] = explode('-', $timeRange, 2);
        $start = trim($start);
        $end = trim($end);

        // Convert to minutes for comparison
        $startMinutes = static::timeToMinutes($start);
        $endMinutes = static::timeToMinutes($end);
        $minMinutes = static::timeToMinutes($minTime);
        $maxMinutes = static::timeToMinutes($maxTime);

        return $startMinutes >= $minMinutes && $endMinutes <= $maxMinutes;
    }

    /**
     * Convert time string to minutes
     */
    private static function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }
}