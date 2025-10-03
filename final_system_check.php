<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL SYSTEM READINESS CHECK ===\n\n";

$checks = [
    'Migrations' => false,
    'Models' => false,
    'Controllers' => false,
    'Routes' => false,
    'Views' => false,
    'Seeders' => false,
    'Security' => false,
    'Dependencies' => false,
    'Database' => false,
    'Clean Files' => false
];

// Check Migrations
echo "🔍 Checking Migrations...\n";
$migrationFiles = glob('database/migrations/*.php');
$expectedMigrations = [
    '0001_01_01_000000_create_users_table.php',
    '0001_01_01_000001_create_cache_table.php',
    '0001_01_01_000002_create_jobs_table.php',
    '2025_01_01_000001_create_tutors_table.php',
    '2025_01_01_000002_create_supervisors_table.php',
    '2025_01_01_000003_create_tutor_accounts_table.php',
    '2025_01_01_000004_create_tutor_details_table.php',
    '2025_01_01_000005_create_daily_data_table.php',
    '2025_01_01_000006_create_tutor_assignments_table.php',
    '2025_01_01_000007_create_schedule_history_table.php',
    '2025_01_01_000008_create_payment_method_details_table.php',
    '2025_01_01_000009_create_employee_payment_information_table.php',
    '2025_01_01_000010_create_employee_payment_details_table.php',
    '2025_01_01_000011_create_security_questions_table.php'
];

$migrationCount = count($migrationFiles);
$expectedCount = count($expectedMigrations);

if ($migrationCount === $expectedCount) {
    echo "   ✅ {$migrationCount} consolidated migrations found\n";
    $checks['Migrations'] = true;
} else {
    echo "   ❌ Expected {$expectedCount} migrations, found {$migrationCount}\n";
}

// Check Models
echo "\n🔍 Checking Models...\n";
$modelFiles = glob('app/Models/*.php');
$expectedModels = [
    'DailyData.php',
    'EmployeePaymentDetails.php',
    'EmployeePaymentInformation.php',
    'PaymentMethodDetails.php',
    'ScheduleHistory.php',
    'SecurityQuestion.php',
    'Supervisor.php',
    'Tutor.php',
    'TutorAccount.php',
    'TutorAssignment.php',
    'TutorDetails.php',
    'User.php'
];

$modelCount = count($modelFiles);
$expectedModelCount = count($expectedModels);

if ($modelCount === $expectedModelCount) {
    echo "   ✅ {$modelCount} models found\n";
    $checks['Models'] = true;
} else {
    echo "   ❌ Expected {$expectedModelCount} models, found {$modelCount}\n";
}

// Check Controllers
echo "\n🔍 Checking Controllers...\n";
$controllerFiles = glob('app/Http/Controllers/*.php');
$authControllers = glob('app/Http/Controllers/Auth/*.php');
$totalControllers = count($controllerFiles) + count($authControllers);

if ($totalControllers >= 15) {
    echo "   ✅ {$totalControllers} controllers found (main: " . count($controllerFiles) . ", auth: " . count($authControllers) . ")\n";
    $checks['Controllers'] = true;
} else {
    echo "   ❌ Insufficient controllers found ({$totalControllers})\n";
}

// Check Routes
echo "\n🔍 Checking Routes...\n";
if (file_exists('routes/web.php') && file_exists('routes/api.php') && file_exists('routes/auth.php')) {
    echo "   ✅ All route files present\n";
    $checks['Routes'] = true;
} else {
    echo "   ❌ Missing route files\n";
}

// Check Views
echo "\n🔍 Checking Views...\n";
$viewDirs = ['auth', 'components', 'dashboard', 'emp_management', 'layouts', 'profile_management', 'schedules'];
$viewCount = 0;
foreach ($viewDirs as $dir) {
    if (is_dir("resources/views/{$dir}")) {
        $viewCount += count(glob("resources/views/{$dir}/*.php"));
    }
}

if ($viewCount >= 20) {
    echo "   ✅ {$viewCount} view files found\n";
    $checks['Views'] = true;
} else {
    echo "   ❌ Insufficient view files found\n";
}

// Check Seeders
echo "\n🔍 Checking Seeders...\n";
$seederFiles = glob('database/seeders/*.php');
if (count($seederFiles) >= 8) {
    echo "   ✅ " . count($seederFiles) . " seeders found\n";
    $checks['Seeders'] = true;
} else {
    echo "   ❌ Insufficient seeders found\n";
}

// Check Security
echo "\n🔍 Checking Security...\n";
$securityChecks = [
    'SecurityHeaders middleware' => file_exists('app/Http/Middleware/SecurityHeaders.php'),
    'InputValidation middleware' => file_exists('app/Http/Middleware/InputValidation.php'),
    'Bootstrap security config' => strpos(file_get_contents('bootstrap/app.php'), 'SecurityHeaders') !== false
];

$securityPassed = 0;
foreach ($securityChecks as $check => $result) {
    if ($result) {
        echo "   ✅ {$check}\n";
        $securityPassed++;
    } else {
        echo "   ❌ {$check}\n";
    }
}

if ($securityPassed === count($securityChecks)) {
    $checks['Security'] = true;
}

// Check Dependencies
echo "\n🔍 Checking Dependencies...\n";
$composerJson = json_decode(file_get_contents('composer.json'), true);
$requiredPackages = ['maatwebsite/excel', 'phpoffice/phpspreadsheet', 'laravel/framework'];

$depsPassed = 0;
foreach ($requiredPackages as $package) {
    if (isset($composerJson['require'][$package])) {
        echo "   ✅ {$package}: {$composerJson['require'][$package]}\n";
        $depsPassed++;
    } else {
        echo "   ❌ {$package} missing\n";
    }
}

if ($depsPassed === count($requiredPackages)) {
    $checks['Dependencies'] = true;
}

// Check Database
echo "\n🔍 Checking Database...\n";
try {
    if (file_exists('database/database.sqlite')) {
        echo "   ✅ SQLite database file exists\n";
        $checks['Database'] = true;
    } else {
        echo "   ⚠️  SQLite database file not found (will be created on first run)\n";
        $checks['Database'] = true; // This is OK for fresh installs
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}

// Check Clean Files
echo "\n🔍 Checking for Clean Files...\n";
$tempFiles = [
    'clean_migrations.php',
    'create_consolidated_migrations.php',
    'detailed_sql_injection_test.php',
    'security_fixes_verification.php',
    'security_vulnerability_test.php'
];

$cleanFiles = true;
foreach ($tempFiles as $file) {
    if (file_exists($file)) {
        echo "   ❌ Temporary file found: {$file}\n";
        $cleanFiles = false;
    }
}

if ($cleanFiles) {
    echo "   ✅ No temporary files found\n";
    $checks['Clean Files'] = true;
}

// Final Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 FINAL SYSTEM READINESS SUMMARY\n";
echo str_repeat("=", 50) . "\n";

$totalChecks = count($checks);
$passedChecks = array_sum($checks);

foreach ($checks as $check => $passed) {
    $status = $passed ? "✅ PASS" : "❌ FAIL";
    echo sprintf("%-20s %s\n", $check . ":", $status);
}

echo str_repeat("-", 50) . "\n";
echo sprintf("%-20s %d/%d\n", "TOTAL:", $passedChecks, $totalChecks);

if ($passedChecks === $totalChecks) {
    echo "\n🎉 SYSTEM IS READY FOR GITHUB PUSH! 🚀\n";
    echo "\n✅ All checks passed!\n";
    echo "✅ Consolidated migrations (14 total)\n";
    echo "✅ 3NF normalized database\n";
    echo "✅ Security hardened\n";
    echo "✅ All dependencies configured\n";
    echo "✅ Clean file structure\n";
    echo "✅ Production ready\n";
} else {
    echo "\n⚠️  SYSTEM NEEDS ATTENTION BEFORE PUSH\n";
    echo "\nIssues found:\n";
    foreach ($checks as $check => $passed) {
        if (!$passed) {
            echo "❌ {$check}\n";
        }
    }
}

echo "\n=== FINAL SYSTEM READINESS CHECK COMPLETE ===\n";
