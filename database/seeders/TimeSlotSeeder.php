<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't truncate if there are existing time slots with foreign key references
        $existingSlots = TimeSlot::count();
        if ($existingSlots > 0) {
            $this->command->info("Found {$existingSlots} existing time slots. Skipping time slot creation to preserve foreign key references.");
            return;
        }

        $days = [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
        ];

        // Define time slots for each day
        $timeSlots = [
            // Morning slots (6AM - 12PM)
            ['startTime' => '06:00:00', 'endTime' => '08:00:00'], // Early morning
            ['startTime' => '08:00:00', 'endTime' => '10:00:00'], // Mid morning
            ['startTime' => '10:00:00', 'endTime' => '12:00:00'], // Late morning
            
            // Afternoon slots (12PM - 6PM)
            ['startTime' => '12:00:00', 'endTime' => '14:00:00'], // Early afternoon
            ['startTime' => '14:00:00', 'endTime' => '16:00:00'], // Mid afternoon
            ['startTime' => '16:00:00', 'endTime' => '18:00:00'], // Late afternoon
            
            // Evening slots (6PM - 12AM)
            ['startTime' => '18:00:00', 'endTime' => '20:00:00'], // Early evening
            ['startTime' => '20:00:00', 'endTime' => '22:00:00'], // Mid evening
            ['startTime' => '22:00:00', 'endTime' => '23:59:59'], // Late evening
        ];

        $createdSlots = [];

        // Create time slots for the current week and next week
        $startDate = Carbon::now()->startOfWeek(); // This week's Monday
        
        for ($week = 0; $week < 2; $week++) { // Current week and next week
            foreach ($days as $dayIndex => $dayName) {
                $currentDate = $startDate->copy()->addDays($dayIndex + ($week * 7));
                
                foreach ($timeSlots as $timeSlot) {
                    $slot = TimeSlot::create([
                        'date' => $currentDate->format('Y-m-d'),
                        'startTime' => $timeSlot['startTime'],
                        'endTime' => $timeSlot['endTime'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $createdSlots[] = [
                        'id' => $slot->timeslotID,
                        'day' => $dayName,
                        'date' => $currentDate->format('Y-m-d'),
                        'time_range' => $timeSlot['startTime'] . ' - ' . $timeSlot['endTime']
                    ];
                }
            }
        }

        $this->command->info('Created ' . count($createdSlots) . ' time slots for 2 weeks (' . count($days) * count($timeSlots) * 2 . ' total slots)');
        
        // Log some examples
        $this->command->info('Sample time slots created:');
        foreach (array_slice($createdSlots, 0, 5) as $slot) {
            $this->command->info("- {$slot['day']} ({$slot['date']}): {$slot['time_range']} [ID: {$slot['id']}]");
        }
    }
}