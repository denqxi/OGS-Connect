<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ScheduleDailyData;

$query = ScheduleDailyData::query();

// Only show schedules with active or unassigned status
$query->where(function($q) {
    $q->whereHas('assignedData', function($sub) {
        $sub->where('class_status', 'active');
    })->orWhereDoesntHave('assignedData');
});

// Show the SQL before adding GROUP BY
echo "=== SQL QUERY (before grouping) ===\n";
echo $query->toSql() . "\n\n";

$data = $query->selectRaw('
        schedules_daily_data.date,
        schedules_daily_data.day,
        schedules_daily_data.school,
        GROUP_CONCAT(DISTINCT schedules_daily_data.class ORDER BY schedules_daily_data.class ASC SEPARATOR ", ") as classes,
        GROUP_CONCAT(DISTINCT schedules_daily_data.time ORDER BY schedules_daily_data.time ASC SEPARATOR ", ") as time_slots,
        GROUP_CONCAT(DISTINCT schedules_daily_data.id ORDER BY schedules_daily_data.id ASC) as schedule_ids
    ')
    ->groupBy('date', 'day', 'school')
    ->orderBy('date', 'asc')
    ->paginate(10)
    ->withQueryString();

echo "=== RESULTS ===\n";
echo "Total records found: " . $data->count() . "\n\n";

foreach ($data as $item) {
    echo "Date: " . $item->date . "\n";
    echo "School: " . $item->school . "\n";
    echo "Classes (raw): " . var_export($item->classes, true) . "\n";
    echo "Time Slots (raw): " . var_export($item->time_slots, true) . "\n";
    echo "Classes (type): " . gettype($item->classes) . "\n";
    echo "Time Slots (type): " . gettype($item->time_slots) . "\n\n";
}

echo "\n=== JSON OUTPUT ===\n";
echo json_encode($data, JSON_PRETTY_PRINT);
