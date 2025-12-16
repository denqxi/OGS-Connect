<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;

echo "\n" . str_repeat("=", 100) . "\n";
echo "                         SYSTEM TESTING - ACCOUNT CREDENTIALS                               \n";
echo str_repeat("=", 100) . "\n";

// Define test password
$testPassword = 'password123';
$hashedPassword = Hash::make($testPassword);

echo "\n📋 UPDATING TEST ACCOUNTS WITH KNOWN PASSWORD\n";
echo "Password being set: {$testPassword}\n";
echo str_repeat("-", 100) . "\n\n";

// Update specific test accounts with known password
$testAccounts = [
    'tutors' => ['OGS-T0001', 'OGS-T0002', 'OGS-T0003'],
    'supervisors' => ['OGS-S1002']
];

echo "=== UPDATING TUTOR TEST ACCOUNTS ===\n";
foreach ($testAccounts['tutors'] as $tutorID) {
    $tutor = Tutor::where('tutorID', $tutorID)->first();
    if ($tutor) {
        $tutor->password = $hashedPassword;
        $tutor->save();
        echo "✓ Updated: {$tutorID} ({$tutor->email})\n";
    } else {
        echo "✗ Not found: {$tutorID}\n";
    }
}

echo "\n=== UPDATING SUPERVISOR TEST ACCOUNTS ===\n";
foreach ($testAccounts['supervisors'] as $supID) {
    $supervisor = Supervisor::where('supID', $supID)->first();
    if ($supervisor) {
        $supervisor->password = $hashedPassword;
        $supervisor->save();
        echo "✓ Updated: {$supID} ({$supervisor->email})\n";
    } else {
        echo "✗ Not found: {$supID}\n";
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "                           TEST ACCOUNT CREDENTIALS                                         \n";
echo str_repeat("=", 100) . "\n\n";

echo "=== TUTOR TEST ACCOUNTS ===\n";
echo str_repeat("-", 100) . "\n";
printf("%-15s %-30s %-20s %s\n", "ID", "Email", "Username", "Password");
echo str_repeat("-", 100) . "\n";

$tutors = Tutor::whereIn('tutorID', $testAccounts['tutors'])->orderBy('tutorID')->get();
foreach ($tutors as $t) {
    printf("%-15s %-30s %-20s %s\n", 
        $t->tutorID,
        $t->email,
        $t->username ?? '(none)',
        $testPassword
    );
}

echo "\n=== SUPERVISOR TEST ACCOUNTS ===\n";
echo str_repeat("-", 100) . "\n";
printf("%-15s %-30s %-25s %s\n", "ID", "Email", "Name", "Password");
echo str_repeat("-", 100) . "\n";

$supervisors = Supervisor::whereIn('supID', $testAccounts['supervisors'])->orderBy('supID')->get();
foreach ($supervisors as $s) {
    $fullName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
    printf("%-15s %-30s %-25s %s\n", 
        $s->supID,
        $s->email,
        $fullName,
        $testPassword
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "QUICK LOGIN CREDENTIALS FOR TESTING\n";
echo str_repeat("=", 100) . "\n\n";

echo "TUTOR LOGIN:\n";
echo "  Email/ID:  OGS-T0003  OR  princerandygonzales@gmail\n";
echo "  Password:  {$testPassword}\n\n";

echo "SUPERVISOR LOGIN:\n";
echo "  Email/ID:  OGS-S1002  OR  dummy@ogsconnect.com\n";
echo "  Password:  {$testPassword}\n\n";

echo str_repeat("=", 100) . "\n";
echo "✓ All test accounts updated and ready for system testing\n";
echo str_repeat("=", 100) . "\n\n";
