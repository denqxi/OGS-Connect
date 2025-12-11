<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleDailyData;
use App\Models\AssignedDailyData;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\Account;
use Carbon\Carbon;

class ScheduleDailyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available tutors and supervisors
        $tutors = Tutor::where('status', 'active')->pluck('tutor_id')->toArray();
        $supervisors = Supervisor::pluck('supervisor_id')->toArray();
        
        // Get actual account names from the accounts table
        $accountNames = Account::pluck('account_name')->toArray();
        
        // If no accounts exist, use default names
        if (empty($accountNames)) {
            $accountNames = ['GLS', 'Tutlo', 'Babilala', 'Talk915'];
        }

        // Sample schools mapped to account names
        $schoolsByAccount = [
            'GLS' => [
                'Oxford Academy',
                'Cambridge International School',
                'Harvard Prep School',
                'Stanford Elementary',
            ],
            'Tutlo' => [
                'MIT Learning Center',
                'Yale High School',
            ],
            'Babilala' => [
                'Princeton Academy',
                'Berkeley Institute',
            ],
            'Talk915' => [
                'Cornell Education Center',
                'Columbia Academy',
            ],
        ];
        
        // Flatten schools array, but keep track of which account they belong to
        $schools = [];
        foreach ($accountNames as $accountName) {
            $normalizedAccount = ucfirst(strtolower($accountName));
            if (isset($schoolsByAccount[$normalizedAccount])) {
                foreach ($schoolsByAccount[$normalizedAccount] as $school) {
                    $schools[$school] = $accountName;
                }
            }
        }
        
        // If no schools mapped, use account names directly as schools
        if (empty($schools)) {
            foreach ($accountNames as $accountName) {
                $schools[ucfirst($accountName) . ' Academy'] = $accountName;
            }
        }

        $classes = [
            'Mathematics 101',
            'English Literature',
            'Science Lab',
            'History Class',
            'Computer Science',
            'Physics Advanced',
            'Chemistry Basics',
            'Biology Studies',
            'Geography',
            'Art & Design'
        ];

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $times = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00'];
        $statuses = ['not_assigned', 'partially_assigned', 'fully_assigned'];

        // Generate schedules for the next 30 days (weekdays only)
        $startDate = Carbon::now();
        $scheduleCount = 0;

        for ($i = 0; $i < 60; $i++) { // Check 60 days to get enough weekdays
            $currentDate = $startDate->copy()->addDays($i);
            
            // Skip weekends
            if ($currentDate->isWeekend()) {
                continue;
            }

            $dayName = $currentDate->format('l'); // Full day name
            $dateString = $currentDate->format('Y-m-d');

            // Create 3-5 random classes per day
            $classesPerDay = rand(3, 5);
            
            for ($j = 0; $j < $classesPerDay; $j++) {
                // Pick a random school and get its associated account
                $schoolNames = array_keys($schools);
                $school = $schoolNames[array_rand($schoolNames)];
                $accountName = $schools[$school];
                
                $class = $classes[array_rand($classes)];
                $time = $times[array_rand($times)];
                $duration = [25, 50, 60][array_rand([25, 50, 60])];

                // Create schedule entry with account reference
                $schedule = ScheduleDailyData::create([
                    'date' => $dateString,
                    'day' => $dayName,
                    'time' => $time,
                    'duration' => $duration,
                    'school' => $accountName, // Use account name from accounts table
                    'class' => $class,
                ]);

                // Randomly assign tutors and supervisors (70% chance of having assignment)
                if (rand(1, 100) <= 70) {
                    $status = $statuses[array_rand($statuses)];
                    
                    $mainTutorId = null;
                    $backupTutorId = null;
                    $supervisorId = !empty($supervisors) ? $supervisors[array_rand($supervisors)] : null;

                    if ($status === 'partially_assigned' || $status === 'fully_assigned') {
                        if (!empty($tutors)) {
                            $mainTutorId = $tutors[array_rand($tutors)];
                        }
                    }

                    if ($status === 'fully_assigned') {
                        if (!empty($tutors) && count($tutors) > 1) {
                            $availableTutors = array_diff($tutors, [$mainTutorId]);
                            if (!empty($availableTutors)) {
                                $backupTutorId = $availableTutors[array_rand($availableTutors)];
                            }
                        }
                    }

                    // Create assignment record
                    AssignedDailyData::create([
                        'schedule_daily_data_id' => $schedule->id,
                        'class_status' => $status,
                        'main_tutor' => $mainTutorId,
                        'backup_tutor' => $backupTutorId,
                        'assigned_supervisor' => $supervisorId,
                        'finalized_at' => null,
                        'finalized_by' => null,
                        'cancelled_at' => null,
                        'notes' => null,
                    ]);
                }

                $scheduleCount++;
            }

            // Stop after we have enough schedules (about 100 entries)
            if ($scheduleCount >= 100) {
                break;
            }
        }

        $this->command->info("Created {$scheduleCount} class schedules successfully!");
    }
}
