<?php

require_once 'vendor/autoload.php';

use App\Models\DailyData;
use App\Models\TutorAssignment;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🗑️  Daily Data Table Cleaner (Force Mode)\n";
echo "==========================================\n\n";

// Get current count before deletion
$dailyDataCount = DailyData::count();
$tutorAssignmentCount = TutorAssignment::count();

echo "Current data in tables:\n";
echo "- Daily Data records: {$dailyDataCount}\n";
echo "- Tutor Assignment records: {$tutorAssignmentCount}\n\n";

if ($dailyDataCount == 0 && $tutorAssignmentCount == 0) {
    echo "✅ Tables are already empty. Nothing to clear.\n";
    exit(0);
}

echo "🔄 Clearing data (no confirmation required)...\n";

try {
    // Start transaction
    DB::beginTransaction();
    
    // Delete tutor assignments first (due to foreign key constraint)
    if ($tutorAssignmentCount > 0) {
        echo "- Deleting {$tutorAssignmentCount} tutor assignments...\n";
        TutorAssignment::query()->delete();
    }
    
    // Delete daily data
    if ($dailyDataCount > 0) {
        echo "- Deleting {$dailyDataCount} daily data records...\n";
        DailyData::query()->delete();
    }
    
    // Commit transaction
    DB::commit();
    
    echo "\n✅ Successfully cleared all data!\n";
    echo "   - Deleted {$tutorAssignmentCount} tutor assignments\n";
    echo "   - Deleted {$dailyDataCount} daily data records\n";
    
} catch (Exception $e) {
    // Rollback on error
    DB::rollback();
    echo "\n❌ Error occurred while clearing data:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Daily data table is now empty and ready for fresh data!\n";
