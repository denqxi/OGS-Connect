<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Application;
use Illuminate\Support\Facades\DB;

echo "Recent Applicants in 'New Applicant' Tab:\n";
echo "==========================================\n\n";

$applications = DB::table('applications')
    ->join('applicants', 'applications.applicant_id', '=', 'applicants.applicant_id')
    ->leftJoin('qualifications', 'applicants.applicant_id', '=', 'qualifications.applicant_id')
    ->leftJoin('work_preferences', 'applicants.applicant_id', '=', 'work_preferences.applicant_id')
    ->select(
        'applications.application_id',
        'applicants.applicant_id',
        'applicants.first_name',
        'applicants.middle_name',
        'applicants.last_name',
        'applicants.email',
        'applicants.contact_number',
        'applicants.interview_time',
        'applications.status',
        'applications.created_at',
        'qualifications.education',
        'work_preferences.platform'
    )
    ->orderBy('applications.created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($applications as $app) {
    $fullName = trim("{$app->first_name} {$app->middle_name} {$app->last_name}");
    $platform = $app->platform ? json_decode($app->platform, true) : [];
    $platformStr = is_array($platform) ? implode(', ', $platform) : 'N/A';
    
    echo "Application ID: {$app->application_id}\n";
    echo "Name: {$fullName}\n";
    echo "Email: {$app->email}\n";
    echo "Contact: {$app->contact_number}\n";
    echo "Status: {$app->status}\n";
    echo "Education: " . ($app->education ?? 'N/A') . "\n";
    echo "Platform: {$platformStr}\n";
    echo "Interview: " . ($app->interview_time ? \Carbon\Carbon::parse($app->interview_time)->format('M d, Y h:i A') : 'N/A') . "\n";
    echo "Submitted: " . \Carbon\Carbon::parse($app->created_at)->format('M d, Y h:i A') . "\n";
    echo "---\n";
}

echo "\nTotal Applications: " . DB::table('applications')->count() . "\n";
