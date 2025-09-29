<?php

namespace Database\Seeders;

use App\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tutors = Tutor::where('status', 'active')->get();
        $timeSlots = DB::table('time_slots')->get();

        $availabilityData = [];

        foreach ($tutors as $tutor) {
            // Each tutor gets 3-5 random available time slots
            $randomSlots = $timeSlots->random(rand(3, 5));
            
            foreach ($randomSlots as $slot) {
                $availabilityData[] = [
                    'tutorID' => $tutor->tutorID,
                    'timeslotID' => $slot->timeslotID,
                    'availStatus' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('availabilities')->insert($availabilityData);

        $this->command->info('✅ Created ' . count($availabilityData) . ' availability records for testing');
    }
}
