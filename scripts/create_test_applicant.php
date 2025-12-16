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

try {
    DB::beginTransaction();

    // 1. Create Applicant
    $timestamp = time();
    $applicant = Applicant::create([
        'first_name' => 'Maria',
        'middle_name' => 'Cruz',
        'last_name' => 'Santos',
        'birth_date' => '1997-05-20',
        'address' => '456 Rizal Avenue, Makati City, Metro Manila',
        'contact_number' => '09987654321',
        'email' => "maria.santos.{$timestamp}@example.com",
        'ms_teams' => "maria.santos.{$timestamp}@teams.example.com",
        'interview_time' => Carbon::now()->addDays(3)->setTime(10, 0),
    ]);

    echo "✓ Created Applicant ID: {$applicant->applicant_id}\n";

    // 2. Create Qualification
    $qualification = Qualification::create([
        'applicant_id' => $applicant->applicant_id,
        'education' => 'bachelor',
        'esl_experience' => '1-2',
        'resume_link' => 'https://drive.google.com/file/d/example-resume-link',
        'intro_video' => 'https://drive.google.com/file/d/example-video-link',
    ]);

    echo "✓ Created Qualification\n";

    // 3. Create Requirement
    $requirement = Requirement::create([
        'applicant_id' => $applicant->applicant_id,
        'resume_link' => 'https://drive.google.com/file/d/example-resume-link',
        'intro_video' => 'https://drive.google.com/file/d/example-intro-video',
        'work_type' => 'work_from_home',
        'speedtest' => 'https://speedtest.net/result/example',
        'main_devices' => 'Laptop, Headset',
        'backup_devices' => 'Phone, Tablet',
    ]);

    echo "✓ Created Requirement\n";

    // 4. Create Work Preference
    $workPreference = WorkPreference::create([
        'applicant_id' => $applicant->applicant_id,
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'days_available' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        'timezone' => 'Asia/Manila',
        'platform' => json_encode(['zoom', 'classin']),
        'can_teach' => json_encode(['kids', 'adults']),
    ]);

    echo "✓ Created Work Preference\n";

    // 5. Create Application
    $application = Application::create([
        'applicant_id' => $applicant->applicant_id,
        'attempt_count' => 0,
        'status' => 'pending',
        'term_agreement' => 1,
        'application_date_time' => now(),
    ]);

    echo "✓ Created Application ID: {$application->application_id}\n";

    DB::commit();

    echo "\n========================================\n";
    echo "SUCCESS! Test Applicant Created\n";
    echo "========================================\n";
    echo "Name: Maria Cruz Santos\n";
    echo "Email: maria.santos.{$timestamp}@example.com\n";
    echo "Contact: 09987654321\n";
    echo "Interview: " . $applicant->interview_time->format('F d, Y h:i A') . "\n";
    echo "Status: Pending\n";
    echo "Application ID: {$application->application_id}\n";
    echo "========================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
