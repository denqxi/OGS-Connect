<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample data arrays for variety (use lowercase to match blade template checks)
        $educations = ['Bachelor\'s Degree in Education', 'Bachelor\'s Degree in English', 'Master\'s Degree in TESOL', 'Bachelor\'s Degree in Communications', 'Associate Degree'];
        $eslExperiences = ['0-1 years', '1-2 years', '2-3 years', '3-5 years', 'No experience'];
        $sources = ['fb_boosting', 'referral', 'fb_boosting', 'referral', 'fb_boosting'];
        $platforms = [['zoom', 'classin'], ['zoom', 'ms_teams'], ['classin', 'voov'], ['zoom'], ['ms_teams', 'others']];
        $canTeach = [['kids', 'adults'], ['adults'], ['kids', 'teenager'], ['teenager', 'adults'], ['kids', 'teenager', 'adults']];
        $workTypes = ['work_from_home', 'work_at_site', 'work_from_home', 'work_from_home', 'work_at_site'];
        $statuses = ['pending', 'pending', 'rejected', 'no_answer', 're_schedule', 'declined', 'not_recommended', 'pending'];
        $daysAvailable = [
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ['monday', 'wednesday', 'friday'],
            ['tuesday', 'thursday', 'saturday'],
            ['monday', 'tuesday', 'wednesday', 'thursday'],
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        ];

        $applicants = [
            [
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Smith',
                'birth_date' => '2000-03-15',
                'address' => '123 Main Street, Quezon City, Metro Manila',
                'contact_number' => '+639171234567',
                'email' => 'john.smith@example.com',
                'ms_teams' => 'john.smith@teams.example.com',
                'interview_time' => Carbon::now()->addDays(2)->setTime(10, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Maria',
                'middle_name' => 'Clara',
                'last_name' => 'Garcia',
                'birth_date' => '1999-07-22',
                'address' => '456 Rizal Avenue, Manila City, Metro Manila',
                'contact_number' => '+639281234568',
                'email' => 'maria.garcia@example.com',
                'ms_teams' => 'maria.garcia@teams.example.com',
                'interview_time' => Carbon::now()->addDays(1)->setTime(14, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'James',
                'middle_name' => 'Patrick',
                'last_name' => 'Reyes',
                'birth_date' => '2001-11-08',
                'address' => '789 Dela Rosa Street, Makati City, Metro Manila',
                'contact_number' => '+639191234569',
                'email' => 'james.reyes@example.com',
                'ms_teams' => 'james.reyes@teams.example.com',
                'interview_time' => Carbon::now()->addDays(3)->setTime(9, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Anna',
                'middle_name' => 'Rose',
                'last_name' => 'Cruz',
                'birth_date' => '2002-01-30',
                'address' => '321 Bonifacio Street, Taguig City, Metro Manila',
                'contact_number' => '+639201234570',
                'email' => 'anna.cruz@example.com',
                'ms_teams' => 'anna.cruz@teams.example.com',
                'interview_time' => Carbon::now()->addDays(2)->setTime(15, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Robert',
                'middle_name' => 'Lee',
                'last_name' => 'Santos',
                'birth_date' => '1998-05-17',
                'address' => '654 EDSA Boulevard, Pasig City, Metro Manila',
                'contact_number' => '+639211234571',
                'email' => 'robert.santos@example.com',
                'ms_teams' => 'robert.santos@teams.example.com',
                'interview_time' => Carbon::now()->addDays(4)->setTime(11, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Jennifer',
                'middle_name' => 'Mae',
                'last_name' => 'Ramos',
                'birth_date' => '2000-09-25',
                'address' => '987 Aguinaldo Highway, Cavite City, Cavite',
                'contact_number' => '+639221234572',
                'email' => 'jennifer.ramos@example.com',
                'ms_teams' => 'jennifer.ramos@teams.example.com',
                'interview_time' => Carbon::now()->addDays(1)->setTime(16, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Michael',
                'middle_name' => 'Angelo',
                'last_name' => 'Bautista',
                'birth_date' => '2001-12-12',
                'address' => '147 MacArthur Highway, Valenzuela City, Metro Manila',
                'contact_number' => '+639231234573',
                'email' => 'michael.bautista@example.com',
                'ms_teams' => 'michael.bautista@teams.example.com',
                'interview_time' => Carbon::now()->addDays(5)->setTime(13, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Sarah',
                'middle_name' => 'Jane',
                'last_name' => 'Flores',
                'birth_date' => '1999-04-18',
                'address' => '258 Commonwealth Avenue, Quezon City, Metro Manila',
                'contact_number' => '+639241234574',
                'email' => 'sarah.flores@example.com',
                'ms_teams' => 'sarah.flores@teams.example.com',
                'interview_time' => Carbon::now()->addDays(3)->setTime(10, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'David',
                'middle_name' => 'Paul',
                'last_name' => 'Mendoza',
                'birth_date' => '2000-06-05',
                'address' => '369 Ortigas Avenue, Pasig City, Metro Manila',
                'contact_number' => '+639251234575',
                'email' => 'david.mendoza@example.com',
                'ms_teams' => 'david.mendoza@teams.example.com',
                'interview_time' => Carbon::now()->addDays(4)->setTime(14, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Patricia',
                'middle_name' => 'Joy',
                'last_name' => 'Torres',
                'birth_date' => '2002-08-20',
                'address' => '741 Roxas Boulevard, Pasay City, Metro Manila',
                'contact_number' => '+639261234576',
                'email' => 'patricia.torres@example.com',
                'ms_teams' => 'patricia.torres@teams.example.com',
                'interview_time' => Carbon::now()->addDays(2)->setTime(11, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Christopher',
                'middle_name' => 'Ryan',
                'last_name' => 'Villanueva',
                'birth_date' => '1997-10-14',
                'address' => '852 Aurora Boulevard, San Juan City, Metro Manila',
                'contact_number' => '+639271234577',
                'email' => 'christopher.villanueva@example.com',
                'ms_teams' => 'christopher.villanueva@teams.example.com',
                'interview_time' => Carbon::now()->addDays(3)->setTime(15, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Elizabeth',
                'middle_name' => 'Grace',
                'last_name' => 'Gonzales',
                'birth_date' => '2001-02-28',
                'address' => '963 C5 Road, Taguig City, Metro Manila',
                'contact_number' => '+639281234578',
                'email' => 'elizabeth.gonzales@example.com',
                'ms_teams' => 'elizabeth.gonzales@teams.example.com',
                'interview_time' => Carbon::now()->addDays(1)->setTime(9, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Daniel',
                'middle_name' => 'Jose',
                'last_name' => 'De Castro',
                'birth_date' => '2000-12-03',
                'address' => '159 Quirino Highway, Caloocan City, Metro Manila',
                'contact_number' => '+639291234579',
                'email' => 'daniel.decastro@example.com',
                'ms_teams' => 'daniel.decastro@teams.example.com',
                'interview_time' => Carbon::now()->addDays(5)->setTime(16, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Lisa',
                'middle_name' => 'Marie',
                'last_name' => 'Aquino',
                'birth_date' => '1998-09-11',
                'address' => '753 España Boulevard, Manila City, Metro Manila',
                'contact_number' => '+639301234580',
                'email' => 'lisa.aquino@example.com',
                'ms_teams' => 'lisa.aquino@teams.example.com',
                'interview_time' => Carbon::now()->addDays(4)->setTime(10, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Matthew',
                'middle_name' => 'Joseph',
                'last_name' => 'Rivera',
                'birth_date' => '2002-04-07',
                'address' => '357 Katipunan Avenue, Quezon City, Metro Manila',
                'contact_number' => '+639311234581',
                'email' => 'matthew.rivera@example.com',
                'ms_teams' => 'matthew.rivera@teams.example.com',
                'interview_time' => Carbon::now()->addDays(2)->setTime(13, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert applicants and related data
        foreach ($applicants as $index => $applicant) {
            $applicantId = DB::table('applicants')->insertGetId($applicant);
            
            // Create corresponding application record with varied statuses
            $status = $statuses[$index % count($statuses)];
            $attemptCount = in_array($status, ['re_schedule', 'no_answer']) ? rand(1, 3) : 0;
            
            $applicationData = [
                'applicant_id' => $applicantId,
                'attempt_count' => $attemptCount,
                'status' => $status,
                'term_agreement' => true,
                'application_date_time' => Carbon::now()->subDays(rand(0, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add notes and interviewer for non-pending applications
            if ($status !== 'pending') {
                $interviewers = ['HR Manager', 'Supervisor A', 'Team Lead B', 'Senior HR'];
                $applicationData['interviewer'] = $interviewers[$index % count($interviewers)];
                
                $notes = match($status) {
                    'rejected' => 'Did not meet minimum qualifications for ESL teaching.',
                    'no_answer' => 'Applicant did not respond to scheduled interview calls.',
                    're_schedule' => 'Applicant requested to reschedule due to prior commitments.',
                    'declined' => 'Applicant declined the position after initial discussion.',
                    'not_recommended' => 'Interview performance was below expected standards.',
                    default => null,
                };
                $applicationData['notes'] = $notes;
            }
            
            DB::table('application')->insert($applicationData);

            // Create qualification record
            DB::table('qualification')->insert([
                'applicant_id' => $applicantId,
                'education' => $educations[$index % count($educations)],
                'esl_experience' => $eslExperiences[$index % count($eslExperiences)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create referral record
            $source = $sources[$index % count($sources)];
            $referrerNames = ['John Doe', 'Maria Santos', 'Peter Cruz', 'Anna Reyes', 'Robert Garcia'];
            DB::table('referral')->insert([
                'applicant_id' => $applicantId,
                'source' => $source,
                'referrer_name' => $source === 'referral' ? $referrerNames[$index % count($referrerNames)] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create work preferences record
            $startTimes = ['08:00:00', '09:00:00', '10:00:00', '07:00:00', '08:30:00'];
            $endTimes = ['17:00:00', '18:00:00', '19:00:00', '16:00:00', '17:30:00'];
            
            DB::table('work_preferences')->insert([
                'applicant_id' => $applicantId,
                'start_time' => $startTimes[$index % count($startTimes)],
                'end_time' => $endTimes[$index % count($endTimes)],
                'days_available' => json_encode($daysAvailable[$index % count($daysAvailable)]),
                'platform' => json_encode($platforms[$index % count($platforms)]),
                'can_teach' => json_encode($canTeach[$index % count($canTeach)]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create requirement record
            DB::table('requirement')->insert([
                'applicant_id' => $applicantId,
                'resume_link' => "https://drive.google.com/file/resume_{$applicantId}",
                'intro_video' => "https://drive.google.com/file/video_{$applicantId}",
                'work_type' => $workTypes[$index % count($workTypes)],
                'speedtest' => "https://speedtest.net/result/{$applicantId}",
                'main_devices' => 'Laptop, Headset, Webcam',
                'backup_devices' => 'Desktop Computer, Backup Headset',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
