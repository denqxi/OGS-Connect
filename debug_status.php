<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the exact data
$schedules = DB::table('schedules_daily_data')->get();
echo "=== SCHEDULES ===\n";
foreach ($schedules as $s) {
    echo "ID: {$s->id} | School: {$s->school} | Date: {$s->date}\n";
}

echo "\n=== ASSIGNMENTS ===\n";
$assignments = DB::table('assigned_daily_data')->get();
foreach ($assignments as $a) {
    echo "ID: {$a->id} | Schedule ID: {$a->schedule_daily_data_id} | Status: {$a->class_status}\n";
}

echo "\n=== CONTROLLER LOGIC TEST ===\n";
$query = DB::table('schedules_daily_data');
$dailyData = $query->orderBy('date')->orderBy('time')->get();

foreach ($dailyData as $data) {
    $assignment = DB::table('assigned_daily_data')
        ->where('schedule_daily_data_id', $data->id)
        ->first();
    
    $status = 'null';
    if ($assignment) {
        $status = $assignment->class_status;
    }
    
    echo "Schedule {$data->id} ({$data->school}): Status = $status\n";
}
