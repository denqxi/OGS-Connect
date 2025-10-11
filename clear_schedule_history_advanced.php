<?php

require_once 'vendor/autoload.php';

use App\Models\ScheduleHistory;
use App\Models\DailyData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🗑️  Advanced Schedule History Data Cleaner\n";
echo "==========================================\n\n";

// Get current count before deletion
$scheduleHistoryCount = ScheduleHistory::count();
$dailyDataCount = DailyData::count();

echo "Current data in tables:\n";
echo "- Schedule History records: {$scheduleHistoryCount}\n";
echo "- Daily Data records: {$dailyDataCount}\n\n";

if ($scheduleHistoryCount == 0 && $dailyDataCount == 0) {
    echo "✅ Tables are already empty. Nothing to clear.\n";
    exit(0);
}

// Show date range of existing data
$oldestSchedule = ScheduleHistory::orderBy('date', 'asc')->first();
$newestSchedule = ScheduleHistory::orderBy('date', 'desc')->first();

if ($oldestSchedule && $newestSchedule) {
    echo "Date range of existing data:\n";
    echo "- Oldest: " . Carbon::parse($oldestSchedule->date)->format('F j, Y') . "\n";
    echo "- Newest: " . Carbon::parse($newestSchedule->date)->format('F j, Y') . "\n\n";
}

// Show options
echo "Choose clearing option:\n";
echo "1. Clear ALL data (Schedule History + Daily Data)\n";
echo "2. Clear data older than 30 days\n";
echo "3. Clear data older than 7 days\n";
echo "4. Clear data for specific date range\n";
echo "5. Cancel\n\n";

echo "Enter your choice (1-5): ";
$option = trim(fgets(STDIN));

if ($option < 1 || $option > 5) {
    echo "❌ Invalid option. Operation cancelled.\n";
    exit(0);
}

if ($option == 5) {
    echo "❌ Operation cancelled.\n";
    exit(0);
}

$deletedCount = 0;
$deletedScheduleHistory = 0;
$deletedDailyData = 0;

try {
    switch ($option) {
        case 1: // Clear ALL data
            echo "\n⚠️  WARNING: This will delete ALL schedule history data!\n";
            echo "Type 'DELETE ALL' to confirm: ";
            $confirmation = trim(fgets(STDIN));
            
            if ($confirmation !== 'DELETE ALL') {
                echo "❌ Operation cancelled.\n";
                exit(0);
            }
            
            echo "\n🔄 Deleting ALL data...\n";
            
            if ($scheduleHistoryCount > 0) {
                $deletedScheduleHistory = ScheduleHistory::count();
                ScheduleHistory::truncate();
                echo "✅ Deleted {$deletedScheduleHistory} Schedule History records\n";
            }
            
            if ($dailyDataCount > 0) {
                $deletedDailyData = DailyData::count();
                DailyData::truncate();
                echo "✅ Deleted {$deletedDailyData} Daily Data records\n";
            }
            break;
            
        case 2: // Clear data older than 30 days
            $cutoffDate = Carbon::now()->subDays(30)->format('Y-m-d');
            echo "\n🔄 Deleting data older than 30 days (before {$cutoffDate})...\n";
            
            $deletedScheduleHistory = ScheduleHistory::where('date', '<', $cutoffDate)->count();
            ScheduleHistory::where('date', '<', $cutoffDate)->delete();
            echo "✅ Deleted {$deletedScheduleHistory} Schedule History records\n";
            
            $deletedDailyData = DailyData::where('date', '<', $cutoffDate)->count();
            DailyData::where('date', '<', $cutoffDate)->delete();
            echo "✅ Deleted {$deletedDailyData} Daily Data records\n";
            break;
            
        case 3: // Clear data older than 7 days
            $cutoffDate = Carbon::now()->subDays(7)->format('Y-m-d');
            echo "\n🔄 Deleting data older than 7 days (before {$cutoffDate})...\n";
            
            $deletedScheduleHistory = ScheduleHistory::where('date', '<', $cutoffDate)->count();
            ScheduleHistory::where('date', '<', $cutoffDate)->delete();
            echo "✅ Deleted {$deletedScheduleHistory} Schedule History records\n";
            
            $deletedDailyData = DailyData::where('date', '<', $cutoffDate)->count();
            DailyData::where('date', '<', $cutoffDate)->delete();
            echo "✅ Deleted {$deletedDailyData} Daily Data records\n";
            break;
            
        case 4: // Clear data for specific date range
            echo "Enter start date (YYYY-MM-DD): ";
            $startDate = trim(fgets(STDIN));
            echo "Enter end date (YYYY-MM-DD): ";
            $endDate = trim(fgets(STDIN));
            
            // Validate dates
            if (!Carbon::createFromFormat('Y-m-d', $startDate) || !Carbon::createFromFormat('Y-m-d', $endDate)) {
                echo "❌ Invalid date format. Operation cancelled.\n";
                exit(0);
            }
            
            echo "\n⚠️  WARNING: This will delete data from {$startDate} to {$endDate}!\n";
            echo "Type 'DELETE RANGE' to confirm: ";
            $confirmation = trim(fgets(STDIN));
            
            if ($confirmation !== 'DELETE RANGE') {
                echo "❌ Operation cancelled.\n";
                exit(0);
            }
            
            echo "\n🔄 Deleting data from {$startDate} to {$endDate}...\n";
            
            $deletedScheduleHistory = ScheduleHistory::whereBetween('date', [$startDate, $endDate])->count();
            ScheduleHistory::whereBetween('date', [$startDate, $endDate])->delete();
            echo "✅ Deleted {$deletedScheduleHistory} Schedule History records\n";
            
            $deletedDailyData = DailyData::whereBetween('date', [$startDate, $endDate])->count();
            DailyData::whereBetween('date', [$startDate, $endDate])->delete();
            echo "✅ Deleted {$deletedDailyData} Daily Data records\n";
            break;
    }
    
    $totalDeleted = $deletedScheduleHistory + $deletedDailyData;
    
    echo "\n🎉 Successfully cleared schedule history data!\n";
    echo "Total records deleted: {$totalDeleted}\n";
    echo "- Schedule History: {$deletedScheduleHistory}\n";
    echo "- Daily Data: {$deletedDailyData}\n";
    
} catch (Exception $e) {
    echo "\n❌ Error occurred during deletion:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Some data may have been deleted before the error occurred.\n";
    exit(1);
}

echo "\n✅ Schedule history data clearing completed successfully!\n";
