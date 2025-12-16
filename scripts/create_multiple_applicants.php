<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Qualification;
use App\Models\Requirement;
use App\Models\WorkPreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$applicants = [
    [
        'first_name' => 'John',
        'middle_name' => 'Paul',
        'last_name' => 'Reyes',
        'birth_date' => '1995-08-12',
        'address' => '789 Aurora Blvd, Pasig City, Metro Manila',
        'contact_number' => '09171234567',
        'education' => 'bachelor',
        'esl_experience' => '3-4',
        'days' => ['monday', 'wednesday', 'friday'],
        'can_teach' => ['kids'],
    ],
    [
        'first_name' => 'Anna',
        'middle_name' => 'Marie',
        'last_name' => 'Garcia',
        'birth_date' => '1999-11-25',
        'address' => '321 Taft Avenue, Manila City, Metro Manila',
        'contact_number' => '09281234567',
        'education' => 'master',
        'esl_experience' => '5plus',
        'days' => ['tuesday', 'thursday', 'saturday'],
        'can_teach' => ['adults'],
    ],
    [
        'first_name' => 'Michael',
        'middle_name' => 'Angelo',
        'last_name' => 'Cruz',
        'birth_date' => '1996-04-30',
        'address' => '555 Ortigas Ave, Mandaluyong City, Metro Manila',
        'contact_number' => '09391234567',
        'education' => 'bachelor',
        'esl_experience' => '1-2',
        'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'can_teach' => ['kids', 'adults'],
    ],
    [
        'first_name' => 'Lisa',
        'middle_name' => 'Joy',
        'last_name' => 'Mendoza',
        'birth_date' => '2000-02-14',
        'address' => '888 Commonwealth Ave, Quezon City, Metro Manila',
        'contact_number' => '09451234567',
        'education' => 'college_undergrad',
        'esl_experience' => 'na',
        'days' => ['monday', 'wednesday', 'friday', 'saturday'],
        'can_teach' => ['kids'],
    ],
    [
        'first_name' => 'Robert',
        'middle_name' => 'James',
        'last_name' => 'Flores',
        'birth_date' => '1994-07-08',
        'address' => '222 EDSA, Makati City, Metro Manila',
        'contact_number' => '09561234567',
        'education' => 'bachelor',
        'esl_experience' => '3-4',
        'days' => ['tuesday', 'thursday', 'saturday', 'sunday'],
        'can_teach' => ['adults'],
    ],
];

$created = 0;
$failed = 0;

foreach ($applicants as $data) {
    try {
        DB::beginTransaction();

        $timestamp = time() + rand(1, 9999);
        
        // Create Applicant
        $applicant = Applicant::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'],
            'contact_number' => $data['contact_number'],
            'email' => strtolower($data['first_name'] . '.' . $data['last_name'] . '.' . $timestamp . '@example.com'),
            'ms_teams' => strtolower($data['first_name'] . '.' . $data['last_name'] . '.' . $timestamp . '@teams.example.com'),
            'interview_time' => Carbon::now()->addDays(rand(1, 7))->setTime(rand(9, 17), [0, 30][rand(0, 1)]),
        ]);

        // Create Qualification
        Qualification::create([
            'applicant_id' => $applicant->applicant_id,
            'education' => $data['education'],
            'esl_experience' => $data['esl_experience'],
            'resume_link' => 'https://drive.google.com/file/d/resume-' . $timestamp,
            'intro_video' => 'https://drive.google.com/file/d/video-' . $timestamp,
        ]);

        // Create Requirement
        Requirement::create([
            'applicant_id' => $applicant->applicant_id,
            'resume_link' => 'https://drive.google.com/file/d/resume-' . $timestamp,
            'intro_video' => 'https://drive.google.com/file/d/video-' . $timestamp,
            'work_type' => 'work_from_home',
            'speedtest' => 'https://speedtest.net/result/' . $timestamp,
            'main_devices' => 'Laptop, Headset, Webcam',
            'backup_devices' => 'Phone, Tablet',
        ]);

        // Create Work Preference
        WorkPreference::create([
            'applicant_id' => $applicant->applicant_id,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'days_available' => json_encode($data['days']),
            'timezone' => 'Asia/Manila',
            'platform' => json_encode(['zoom', 'classin']),
            'can_teach' => json_encode($data['can_teach']),
        ]);

        // Create Application
        $application = Application::create([
            'applicant_id' => $applicant->applicant_id,
            'attempt_count' => 0,
            'status' => 'pending',
            'term_agreement' => 1,
            'application_date_time' => now(),
        ]);

        DB::commit();
        
        $fullName = "{$data['first_name']} {$data['middle_name']} {$data['last_name']}";
        echo "✓ Created: {$fullName} (Application ID: {$application->application_id})\n";
        $created++;
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "✗ Failed: {$data['first_name']} {$data['last_name']} - " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n========================================\n";
echo "Batch Creation Complete\n";
echo "========================================\n";
echo "Successfully created: {$created} applicants\n";
echo "Failed: {$failed} applicants\n";
echo "========================================\n";
