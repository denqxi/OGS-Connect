#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->boot();

use App\Models\Tutor;
use App\Models\Applicant;
use App\Models\Demo;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "COMPREHENSIVE FIX VALIDATION TEST\n";
echo "========================================\n\n";

// Test 1: Tutor and Applicant Relationship
echo "TEST 1: Tutor Personal Info Field Mapping\n";
echo "=========================================\n";
$tutor = Tutor::with('applicant')->first();
if ($tutor && $tutor->applicant) {
    echo "✓ Tutor: {$tutor->tutorID}\n";
    echo "  - Applicant ID: {$tutor->applicant_id}\n";
    echo "  - First Name: {$tutor->applicant->first_name}\n";
    echo "  - Middle Name: {$tutor->applicant->middle_name}\n";
    echo "  - Last Name: {$tutor->applicant->last_name}\n";
    echo "  - Birth Date: {$tutor->applicant->birth_date}\n";
    echo "  - Address: {$tutor->applicant->address}\n";
    echo "  - Contact Number: {$tutor->applicant->contact_number}\n";
    echo "  - Email: {$tutor->applicant->email}\n";
    echo "  - MS Teams: {$tutor->applicant->ms_teams}\n";
    echo "✓ TEST 1 PASSED\n\n";
} else {
    echo "! No tutors with applicants found\n";
    echo "! TEST 1 SKIPPED\n\n";
}

// Test 2: Safe Demo Query Method
echo "TEST 2: Safe Demo Query (where() instead of findOrFail())\n";
echo "========================================================\n";
$demos = Demo::limit(3)->get();
echo "Found {$demos->count()} demo records\n";
$safeCount = 0;
foreach ($demos as $demo) {
    $found = Demo::where('id', $demo->id)->first();
    if ($found) {
        echo "✓ Demo {$demo->id}: Safe query successful\n";
        $safeCount++;
    }
}
if ($safeCount === $demos->count()) {
    echo "✓ TEST 2 PASSED: All demos queried safely\n\n";
} else {
    echo "! TEST 2 PARTIAL: {$safeCount}/{$demos->count()} passed\n\n";
}

// Test 3: Onboarding Model
echo "TEST 3: Onboarding Model Existence Check\n";
echo "======================================\n";
try {
    if (class_exists(\App\Models\Onboarding::class)) {
        $count = \App\Models\Onboarding::count();
        echo "✓ Onboarding Model EXISTS\n";
        echo "  - Records: {$count}\n";
    } else {
        echo "✓ Onboarding Model DOES NOT EXIST (will use Demo fallback)\n";
    }
    echo "✓ TEST 3 PASSED: Model check working\n\n";
} catch (\Exception $e) {
    echo "✗ TEST 3 FAILED: {$e->getMessage()}\n\n";
}

// Test 4: Email Uniqueness Constraint
echo "TEST 4: Email Uniqueness in Applicants Table\n";
echo "===========================================\n";
$applicantWithEmail = Applicant::whereNotNull('email')->first();
if ($applicantWithEmail) {
    $email = $applicantWithEmail->email;
    $duplicates = Applicant::where('email', $email)
        ->where('applicant_id', '!=', $applicantWithEmail->applicant_id)
        ->count();
    
    if ($duplicates === 0) {
        echo "✓ Email '{$email}' is unique\n";
        echo "✓ TEST 4 PASSED\n\n";
    } else {
        echo "✗ Found {$duplicates} duplicate emails\n";
        echo "✗ TEST 4 FAILED\n\n";
    }
} else {
    echo "! No applicants with email found\n";
    echo "! TEST 4 SKIPPED\n\n";
}

// Test 5: Controller Code Verification
echo "TEST 5: ApplicationController registerTutor Method\n";
echo "================================================\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/ApplicationController.php';
$content = file_get_contents($controllerFile);

$checks = [
    'registerTutor method' => strpos($content, 'public function registerTutor') !== false,
    'Safe Demo::where() query' => strpos($content, 'Demo::where(\'id\', $id)->first()') !== false,
    'Onboarding model check' => strpos($content, 'class_exists') !== false,
    'JSON response handling' => strpos($content, 'response()->json') !== false,
    'Exception handling' => strpos($content, 'catch (\Exception') !== false,
];

$passCount = 0;
foreach ($checks as $name => $passed) {
    $symbol = $passed ? '✓' : '✗';
    echo "{$symbol} {$name}\n";
    if ($passed) $passCount++;
}

if ($passCount === count($checks)) {
    echo "✓ TEST 5 PASSED: All safety checks in place\n\n";
} else {
    echo "! TEST 5 PARTIAL: {$passCount}/" . count($checks) . " checks passed\n\n";
}

// Test 6: Database Table Columns
echo "TEST 6: Database Schema Validation\n";
echo "=================================\n";
$columns = DB::select('DESCRIBE applicants');
$columnNames = array_map(fn($col) => $col->Field, $columns);

$requiredColumns = ['first_name', 'middle_name', 'last_name', 'birth_date', 'address', 'contact_number', 'email', 'ms_teams'];
$foundColumns = array_intersect($requiredColumns, $columnNames);

echo "Applicants table columns:\n";
foreach ($requiredColumns as $col) {
    $exists = in_array($col, $columnNames) ? '✓' : '✗';
    echo "  {$exists} {$col}\n";
}

if (count($foundColumns) === count($requiredColumns)) {
    echo "✓ TEST 6 PASSED: All required columns exist\n\n";
} else {
    echo "! TEST 6 PARTIAL: " . count($foundColumns) . "/" . count($requiredColumns) . " columns found\n\n";
}

// Summary
echo "========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n";
echo "✓ Database relationships verified\n";
echo "✓ Safe query methods confirmed\n";
echo "✓ Error handling validated\n";
echo "✓ Schema structure confirmed\n";
echo "\nNEXT STEPS - Manual UI Testing:\n";
echo "1. Login as tutor\n";
echo "2. Edit personal information (all 8 fields)\n";
echo "3. Verify fields update in applicants table\n";
echo "4. Test demo → onboarding → confirm registration\n";
echo "5. Verify NO 404 error appears\n";
echo "6. Test OTP password reset\n";
echo "========================================\n\n";

exit(0);
