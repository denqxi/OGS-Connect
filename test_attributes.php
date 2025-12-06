<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ScheduleDailyData;

$query = ScheduleDailyData::query();

$query->where(function($q) {
    $q->whereHas('assignedData', function($sub) {
        $sub->where('class_status', 'active');
    })->orWhereDoesntHave('assignedData');
});

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
    ->paginate(10);

echo "=== FIRST ITEM AS OBJECT ===\n";
$first = $data->first();
echo "classes (via ->classes): " . var_export($first->classes, true) . "\n";
echo "time_slots (via ->time_slots): " . var_export($first->time_slots, true) . "\n";
echo "classes (via getAttribute): " . var_export($first->getAttribute('classes'), true) . "\n";
echo "time_slots (via getAttribute): " . var_export($first->getAttribute('time_slots'), true) . "\n";

echo "\n=== FIRST ITEM AS ARRAY ===\n";
$array = $first->toArray();
echo "classes: " . var_export($array['classes'] ?? 'NOT SET', true) . "\n";
echo "time_slots: " . var_export($array['time_slots'] ?? 'NOT SET', true) . "\n";

echo "\n=== RAW ATTRIBUTES ===\n";
echo "getAttributes(): " . json_encode($first->getAttributes(), JSON_PRETTY_PRINT) . "\n";
