<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Query assigned_daily_data
echo "=== ASSIGNED DAILY DATA ===\n";
$assignments = DB::table('assigned_daily_data')
    ->join('schedules_daily_data', 'assigned_daily_data.schedule_daily_data_id', '=', 'schedules_daily_data.id')
    ->select('assigned_daily_data.id', 'assigned_daily_data.class_status', 'schedules_daily_data.school', 'schedules_daily_data.date')
    ->get();

foreach ($assignments as $a) {
    echo "ID: {$a->id} | School: {$a->school} | Date: {$a->date} | Status: {$a->class_status}\n";
}

echo "\n=== CONTROLLER SIMULATION ===\n";
$dailyData = App\Models\ScheduleDailyData::with('assignedData')->get();

foreach ($dailyData as $data) {
    $assignment = $data->assignedData;
    
    if ($assignment) {
        echo "Schedule ID: {$data->id} | School: {$data->school}\n";
        echo "  Assignment ID: {$assignment->id}\n";
        echo "  DB class_status: {$assignment->class_status}\n";
        
        // Simulate controller logic
        if ($assignment->class_status === 'partially_assigned') {
            $data->setAttribute('status', 'Partially Assigned');
            $data->setAttribute('status_color', 'bg-yellow-100 text-yellow-800');
        }
        
        echo "  After setAttribute - status: {$data->status}\n";
        echo "  After setAttribute - status_color: {$data->status_color}\n\n";
    }
}
