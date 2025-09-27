<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutor;
use App\Models\TimeSlot;
use App\Models\Availability;
use Carbon\Carbon;

class TutorAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't truncate existing availabilities, just add more
        $existingAvailabilities = Availability::count();
        if ($existingAvailabilities > 0) {
            $this->command->info("Found {$existingAvailabilities} existing availability records. Adding to existing data.");
        }

        $tutors = Tutor::all();
        
        if ($tutors->isEmpty()) {
            $this->command->error('No tutors found. Please run TutorSeeder first.');
            return;
        }

        $timeSlots = TimeSlot::all();
        
        if ($timeSlots->isEmpty()) {
            $this->command->error('No time slots found. Please run TimeSlotSeeder first.');
            return;
        }

        // Group time slots by day for easier access
        $timeSlotsByDay = $timeSlots->groupBy(function($slot) {
            return Carbon::parse($slot->date)->format('l'); // Day name (Monday, Tuesday, etc.)
        });

        // Define availability patterns for different tutor types
        $availabilityPatterns = [
            'morning_person' => [
                'Monday' => ['06:00:00-08:00:00', '08:00:00-10:00:00', '10:00:00-12:00:00'],
                'Tuesday' => ['06:00:00-08:00:00', '08:00:00-10:00:00'],
                'Wednesday' => ['08:00:00-10:00:00', '10:00:00-12:00:00'],
                'Thursday' => ['06:00:00-08:00:00', '10:00:00-12:00:00'],
                'Friday' => ['08:00:00-10:00:00', '10:00:00-12:00:00'],
                'Saturday' => ['06:00:00-08:00:00', '08:00:00-10:00:00'],
                'Sunday' => ['10:00:00-12:00:00'],
            ],
            'afternoon_person' => [
                'Monday' => ['12:00:00-14:00:00', '14:00:00-16:00:00', '16:00:00-18:00:00'],
                'Tuesday' => ['14:00:00-16:00:00', '16:00:00-18:00:00'],
                'Wednesday' => ['12:00:00-14:00:00', '16:00:00-18:00:00'],
                'Thursday' => ['12:00:00-14:00:00', '14:00:00-16:00:00'],
                'Friday' => ['14:00:00-16:00:00', '16:00:00-18:00:00'],
                'Saturday' => ['12:00:00-14:00:00', '14:00:00-16:00:00'],
                'Sunday' => ['14:00:00-16:00:00'],
            ],
            'evening_person' => [
                'Monday' => ['18:00:00-20:00:00', '20:00:00-22:00:00'],
                'Tuesday' => ['18:00:00-20:00:00', '20:00:00-22:00:00'],
                'Wednesday' => ['18:00:00-20:00:00', '22:00:00-23:59:59'],
                'Thursday' => ['20:00:00-22:00:00', '22:00:00-23:59:59'],
                'Friday' => ['18:00:00-20:00:00', '20:00:00-22:00:00', '22:00:00-23:59:59'],
                'Saturday' => ['18:00:00-20:00:00', '20:00:00-22:00:00'],
                'Sunday' => ['18:00:00-20:00:00'],
            ],
            'flexible' => [
                'Monday' => ['08:00:00-10:00:00', '14:00:00-16:00:00', '18:00:00-20:00:00'],
                'Tuesday' => ['06:00:00-08:00:00', '12:00:00-14:00:00', '20:00:00-22:00:00'],
                'Wednesday' => ['10:00:00-12:00:00', '16:00:00-18:00:00'],
                'Thursday' => ['08:00:00-10:00:00', '14:00:00-16:00:00'],
                'Friday' => ['12:00:00-14:00:00', '18:00:00-20:00:00'],
                'Saturday' => ['10:00:00-12:00:00', '14:00:00-16:00:00', '20:00:00-22:00:00'],
                'Sunday' => ['08:00:00-10:00:00', '16:00:00-18:00:00'],
            ],
        ];

        $patterns = array_keys($availabilityPatterns);
        $createdAvailabilities = [];

        foreach ($tutors as $index => $tutor) {
            // Assign a pattern to each tutor cyclically
            $patternKey = $patterns[$index % count($patterns)];
            $pattern = $availabilityPatterns[$patternKey];

            $this->command->info("Assigning '{$patternKey}' pattern to tutor: {$tutor->tusername}");

            foreach ($pattern as $dayName => $timeRanges) {
                if (!isset($timeSlotsByDay[$dayName])) {
                    continue;
                }

                $dayTimeSlots = $timeSlotsByDay[$dayName];

                foreach ($timeRanges as $timeRange) {
                    [$startTime, $endTime] = explode('-', $timeRange);
                    
                    // Find matching time slots for this time range
                    $matchingSlots = $dayTimeSlots->filter(function($slot) use ($startTime, $endTime) {
                        // Extract time part from datetime stamps for comparison
                        $slotStartTime = Carbon::parse($slot->startTime)->format('H:i:s');
                        $slotEndTime = Carbon::parse($slot->endTime)->format('H:i:s');
                        return $slotStartTime === $startTime && $slotEndTime === $endTime;
                    });

                    foreach ($matchingSlots as $slot) {
                        // Check if this availability already exists
                        $existingAvailability = Availability::where('tutorID', $tutor->tutorID)
                                                           ->where('timeslotID', $slot->timeslotID)
                                                           ->first();
                        
                        if ($existingAvailability) {
                            continue; // Skip if already exists
                        }

                        // Create availability with 90% chance of being available
                        $status = (rand(1, 100) <= 90) ? 'available' : 'unavailable';
                        
                        $availability = Availability::create([
                            'tutorID' => $tutor->tutorID,
                            'timeslotID' => $slot->timeslotID,
                            'availStatus' => $status,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $createdAvailabilities[] = [
                            'tutor' => $tutor->tusername,
                            'day' => $dayName,
                            'time' => $timeRange,
                            'status' => $status,
                            'date' => $slot->date,
                            'slot_id' => $slot->timeslotID,
                        ];
                    }
                }
            }
        }

        $this->command->info('Created ' . count($createdAvailabilities) . ' availability records');
        
        // Show summary by tutor
        $availabilityByTutor = collect($createdAvailabilities)->groupBy('tutor');
        foreach ($availabilityByTutor as $tutorName => $availabilities) {
            $available = $availabilities->where('status', 'available')->count();
            $total = $availabilities->count();
            $this->command->info("  - {$tutorName}: {$available}/{$total} available slots");
        }

        // Show summary by day
        $this->command->info('Availability by day:');
        $availabilityByDay = collect($createdAvailabilities)->groupBy('day');
        foreach ($availabilityByDay as $day => $availabilities) {
            $available = $availabilities->where('status', 'available')->count();
            $total = $availabilities->count();
            $this->command->info("  - {$day}: {$available}/{$total} available slots");
        }
    }
}