<?php

require_once 'vendor/autoload.php';

use App\Models\ScheduleHistory;
use App\Models\DailyData;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🗑️  Schedule History Data Cleaner\n";
echo "==================================\n\n";

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

// Ask for confirmation
echo "⚠️  WARNING: This will permanently delete all data from:\n";
echo "- Schedule History table\n";
echo "- Daily Data table\n\n";

echo "This action cannot be undone!\n\n";

echo "Type 'CLEAR' to confirm deletion: ";
$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'CLEAR') {
    echo "❌ Operation cancelled. No data was deleted.\n";
    exit(0);
}

echo "\n🔄 Starting deletion process...\n\n";

try {
    // Delete schedule history records
    if ($scheduleHistoryCount > 0) {
        echo "Deleting Schedule History records...\n";
        ScheduleHistory::truncate();
        echo "✅ Deleted {$scheduleHistoryCount} Schedule History records\n";
    }
    
    // Delete daily data records
    if ($dailyDataCount > 0) {
        echo "Deleting Daily Data records...\n";
        DailyData::truncate();
        echo "✅ Deleted {$dailyDataCount} Daily Data records\n";
    }
    
    echo "\n🎉 Successfully cleared all schedule history data!\n";
    echo "Total records deleted: " . ($scheduleHistoryCount + $dailyDataCount) . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Error occurred during deletion:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Some data may have been deleted before the error occurred.\n";
    exit(1);
}

echo "\n✅ Schedule history data clearing completed successfully!\n";
