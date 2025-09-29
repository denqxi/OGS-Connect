<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create time slots for the next week (Monday to Friday)
        $startDate = now()->startOfWeek(); // Monday
        $timeSlots = [];
        
        $timeRanges = [
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'],
            ['16:00:00', '17:00:00'],
        ];
        
        // Create time slots for Monday to Friday
        for ($day = 0; $day < 5; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            foreach ($timeRanges as $range) {
                $timeSlots[] = [
                    'date' => $date->format('Y-m-d'),
                    'startTime' => $range[0],
                    'endTime' => $range[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('time_slots')->insert($timeSlots);

        $this->command->info('✅ Created ' . count($timeSlots) . ' time slots for testing');
    }
}
