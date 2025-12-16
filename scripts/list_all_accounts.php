<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tutor;
use App\Models\Supervisor;

echo "\n=== TUTOR ACCOUNTS ===\n";
echo str_repeat("=", 80) . "\n";

$tutors = Tutor::select('tutor_id', 'tutorID', 'username', 'email', 'status', 'applicant_id')
    ->orderBy('tutorID')
    ->get();

if ($tutors->count() > 0) {
    printf("%-12s %-20s %-25s %-15s %s\n", "ID", "TutorID", "Email", "Username", "Status");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($tutors as $t) {
        printf("%-12s %-20s %-25s %-15s %s\n", 
            $t->tutor_id,
            $t->tutorID ?? 'N/A',
            substr($t->email ?? 'N/A', 0, 25),
            $t->username ?? 'N/A',
            $t->status ?? 'N/A'
        );
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "Total Tutors: " . $tutors->count() . "\n\n";
} else {
    echo "No tutors found.\n\n";
}

echo "\n=== SUPERVISOR ACCOUNTS ===\n";
echo str_repeat("=", 80) . "\n";

$supervisors = Supervisor::select('supervisor_id', 'supID', 'email', 'status', 'first_name', 'last_name')
    ->orderBy('supID')
    ->get();

if ($supervisors->count() > 0) {
    printf("%-12s %-20s %-30s %-20s %s\n", "ID", "SupID", "Email", "Name", "Status");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($supervisors as $s) {
        $fullName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')) ?: 'N/A';
        printf("%-12s %-20s %-30s %-20s %s\n", 
            $s->supervisor_id,
            $s->supID ?? 'N/A',
            substr($s->email ?? 'N/A', 0, 30),
            substr($fullName, 0, 20),
            $s->status ?? 'N/A'
        );
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "Total Supervisors: " . $supervisors->count() . "\n\n";
} else {
    echo "No supervisors found.\n\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total Tutors: " . $tutors->count() . "\n";
echo "Total Supervisors: " . $supervisors->count() . "\n";
echo "Total Accounts: " . ($tutors->count() + $supervisors->count()) . "\n";
echo "\n";
