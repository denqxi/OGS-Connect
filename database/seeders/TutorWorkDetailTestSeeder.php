<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TutorWorkDetail;
use App\Models\AssignedDailyData;
use App\Models\Tutor;

class TutorWorkDetailTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Michael Brown tutor
        $tutor = Tutor::where('username', 'michaelbrown')->first();
        
        if (!$tutor) {
            $this->command->info('Michael Brown tutor (michaelbrown) not found. Skipping work detail seeding.');
            return;
        }

        // Find assigned schedules for this tutor (where main_tutor = tutor_id)
        $assignments = AssignedDailyData::where('main_tutor', $tutor->tutor_id)->get();

        if ($assignments->isEmpty()) {
            $this->command->info('No assignments found for Michael Brown (michaelbrown). Skipping work detail seeding.');
            return;
        }

        // Create work details for each assignment
        foreach ($assignments as $assignment) {
            // Check if work detail already exists for this assignment
            $exists = TutorWorkDetail::where('assignment_id', $assignment->id)->exists();
            
            if (!$exists) {
                TutorWorkDetail::create([
                    'tutor_id' => $tutor->tutorID,
                    'assignment_id' => $assignment->id,
                    'schedule_daily_data_id' => $assignment->schedule_daily_data_id,
                    'start_time' => '13:00', // 1:00 PM
                    'end_time' => '13:25',   // 1:25 PM
                    'duration_minutes' => 25,
                    'proof_image' => 'tutor_work_screenshots/sample.jpg',
                    'status' => 'pending',
                    'work_type' => 'per class',
                    'rate_per_hour' => 0,
                    'rate_per_class' => 50,
                    'note' => 'Class completed successfully'
                ]);

                $this->command->info("Created work detail for assignment {$assignment->id} (Schedule: {$assignment->schedule_daily_data_id})");
            }
        }

        $this->command->info('Work detail seeding completed.');

    }
}
