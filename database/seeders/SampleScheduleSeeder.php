<?php

namespace Database\Seeders;

use App\Models\DailyData;
use Illuminate\Database\Seeder;

class SampleScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleSchedules = [
            [
                'date' => '2025-09-02',
                'day' => 'Monday',
                'school' => 'Tokyo Elementary',
                'class' => 'Grade 3 Math',
                'time_jst' => '09:00:00',
                'time_pht' => '08:00:00',
                'duration' => 25,
                'number_required' => 2,
                'schedule_status' => 'tentative',
                'class_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-09-02',
                'day' => 'Monday',
                'school' => 'Tokyo Elementary',
                'class' => 'Grade 4 English',
                'time_jst' => '10:00:00',
                'time_pht' => '09:00:00',
                'duration' => 25,
                'number_required' => 1,
                'schedule_status' => 'tentative',
                'class_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-09-02',
                'day' => 'Monday',
                'school' => 'Osaka Middle School',
                'class' => 'Grade 7 Science',
                'time_jst' => '14:00:00',
                'time_pht' => '13:00:00',
                'duration' => 25,
                'number_required' => 3,
                'schedule_status' => 'tentative',
                'class_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-09-03',
                'day' => 'Tuesday',
                'school' => 'Kyoto High School',
                'class' => 'Grade 10 History',
                'time_jst' => '15:00:00',
                'time_pht' => '14:00:00',
                'duration' => 25,
                'number_required' => 2,
                'schedule_status' => 'tentative',
                'class_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2025-09-03',
                'day' => 'Tuesday',
                'school' => 'Hiroshima Elementary',
                'class' => 'Grade 2 Art',
                'time_jst' => '11:00:00',
                'time_pht' => '10:00:00',
                'duration' => 25,
                'number_required' => 1,
                'schedule_status' => 'tentative',
                'class_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sampleSchedules as $schedule) {
            DailyData::create($schedule);
        }

        $this->command->info('✅ Created ' . count($sampleSchedules) . ' sample schedule entries for testing');
    }
}
