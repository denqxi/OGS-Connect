<?php

require_once 'vendor/autoload.php';

use App\Models\Tutor;
use App\Models\TutorAccount;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Starting GLS tutor time slot update with proper rules...\n";

// Get all GLS tutors
$glsTutors = Tutor::whereHas('accounts', function($q) {
    $q->where('account_name', 'GLS');
})->with('accounts')->get();

echo "Found " . $glsTutors->count() . " GLS tutors to update\n";

// GLS Rules: Weekdays only (Monday-Friday), 7:00 AM to 3:00 PM
$allowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Generate random time slots respecting GLS rules (7:00 AM to 3:00 PM, more than 1 hour)
function generateRandomTimeSlot() {
    // Random start hour between 7 AM and 2 PM (to ensure we don't go past 3 PM)
    $startHour = rand(7, 14);
    $startMinute = rand(0, 59);
    
    // Duration between 1.5 to 2.5 hours (90 to 150 minutes)
    // This ensures we stay within the 3 PM limit
    $durationMinutes = rand(90, 150);
    
    $startTime = sprintf('%02d:%02d', $startHour, $startMinute);
    
    // Calculate end time
    $totalMinutes = $startHour * 60 + $startMinute + $durationMinutes;
    $endHour = intval($totalMinutes / 60);
    $endMinute = $totalMinutes % 60;
    
    // Make sure we don't go past 3 PM (15:00)
    if ($endHour > 15 || ($endHour == 15 && $endMinute > 0)) {
        $endHour = 15;
        $endMinute = 0;
    }
    
    $endTime = sprintf('%02d:%02d', $endHour, $endMinute);
    
    return $startTime . '-' . $endTime;
}

$updated = 0;
foreach ($glsTutors as $tutor) {
    $glsAccount = $tutor->accounts->where('account_name', 'GLS')->first();
    if ($glsAccount) {
        // Random days (2-5 weekdays per week)
        $numDays = rand(2, 5);
        $selectedDays = array_rand($allowedDays, $numDays);
        if (!is_array($selectedDays)) {
            $selectedDays = [$selectedDays];
        }
        $availableDays = array_map(function($index) use ($allowedDays) {
            return $allowedDays[$index];
        }, $selectedDays);
        
        // Generate time slots for each day (respecting 7:00 AM to 3:00 PM rule)
        $availableTimes = [];
        foreach ($availableDays as $day) {
            $availableTimes[$day] = [generateRandomTimeSlot()];
        }
        
        // Update the account
        $glsAccount->update([
            'available_days' => $availableDays,
            'available_times' => $availableTimes
        ]);
        
        $updated++;
        echo "Updated " . $tutor->full_name . " - Days: " . implode(', ', $availableDays) . "\n";
        
        // Show sample time slots for verification
        $sampleDay = $availableDays[0];
        $sampleTime = $availableTimes[$sampleDay][0];
        echo "  Sample time slot: " . $sampleDay . " " . $sampleTime . "\n";
    }
}

echo "Successfully updated " . $updated . " GLS tutors with rule-compliant time slots\n";
echo "All time slots are between 7:00 AM and 3:00 PM on weekdays only\n";
