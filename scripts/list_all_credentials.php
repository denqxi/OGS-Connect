<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tutor;
use App\Models\Supervisor;

echo "\n" . str_repeat("=", 100) . "\n";
echo "                              ALL ACCOUNT CREDENTIALS                                    \n";
echo str_repeat("=", 100) . "\n";

echo "\n=== TUTOR ACCOUNTS ===\n";
echo str_repeat("-", 100) . "\n";

$tutors = Tutor::select('tutor_id', 'tutorID', 'username', 'email', 'password', 'status')
    ->orderBy('tutorID')
    ->get();

if ($tutors->count() > 0) {
    printf("%-15s %-25s %-20s %-15s %s\n", "TutorID", "Email", "Username", "Status", "Password Hash");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($tutors as $t) {
        printf("%-15s %-25s %-20s %-15s %s\n", 
            $t->tutorID ?? 'N/A',
            substr($t->email ?? 'N/A', 0, 25),
            $t->username ?? 'N/A',
            $t->status ?? 'N/A',
            substr($t->password ?? 'N/A', 0, 40) . '...'
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo "Total Tutors: " . $tutors->count() . "\n\n";
} else {
    echo "No tutors found.\n\n";
}

echo "\n=== SUPERVISOR ACCOUNTS ===\n";
echo str_repeat("-", 100) . "\n";

$supervisors = Supervisor::select('supervisor_id', 'supID', 'email', 'password', 'status', 'first_name', 'last_name')
    ->orderBy('supID')
    ->get();

if ($supervisors->count() > 0) {
    printf("%-15s %-30s %-25s %-15s %s\n", "SupID", "Email", "Name", "Status", "Password Hash");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($supervisors as $s) {
        $fullName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: 'N/A';
        printf("%-15s %-30s %-25s %-15s %s\n", 
            $s->supID ?? 'N/A',
            substr($s->email ?? 'N/A', 0, 30),
            substr($fullName, 0, 25),
            $s->status ?? 'N/A',
            substr($s->password ?? 'N/A', 0, 40) . '...'
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo "Total Supervisors: " . $supervisors->count() . "\n\n";
} else {
    echo "No supervisors found.\n\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Total Tutors:      " . $tutors->count() . "\n";
echo "Total Supervisors: " . $supervisors->count() . "\n";
echo "Total Accounts:    " . ($tutors->count() + $supervisors->count()) . "\n";
echo "\nNOTE: Passwords are bcrypt hashed and cannot be reversed.\n";
echo "      Use the password reset feature to set new passwords.\n";
echo str_repeat("=", 100) . "\n\n";
