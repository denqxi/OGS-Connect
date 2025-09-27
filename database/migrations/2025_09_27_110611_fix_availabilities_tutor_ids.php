<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "Fixing numeric tutorIDs in availabilities table...\n";
        
        // Find all numeric tutorID values in availabilities
        $numericRefs = \Illuminate\Support\Facades\DB::table('availabilities')
            ->selectRaw('DISTINCT tutorID')
            ->whereRaw('tutorID REGEXP \'^[0-9]+$\'')
            ->get();
            
        foreach ($numericRefs as $ref) {
            $numericId = $ref->tutorID;
            $formattedId = 'OGS-T' . str_pad($numericId, 4, '0', STR_PAD_LEFT);
            
            // Check if this formatted ID exists in the tutors table
            $tutorExists = \Illuminate\Support\Facades\DB::table('tutors')
                ->where('tutorID', $formattedId)
                ->exists();
                
            if ($tutorExists) {
                // Update all records with this numeric ID to the formatted ID
                $updatedCount = \Illuminate\Support\Facades\DB::table('availabilities')
                    ->where('tutorID', $numericId)
                    ->update(['tutorID' => $formattedId]);
                    
                echo "Updated {$updatedCount} availability records: {$numericId} → {$formattedId}\n";
            } else {
                // Delete orphaned records where the tutor no longer exists
                $deletedCount = \Illuminate\Support\Facades\DB::table('availabilities')
                    ->where('tutorID', $numericId)
                    ->delete();
                    
                echo "Deleted {$deletedCount} orphaned availability records for tutorID: {$numericId} (tutor doesn't exist)\n";
            }
        }
        
        echo "Availability tutorID conversion completed.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely rollback this data transformation
        echo "Cannot rollback availability tutorID fixes - this corrects orphaned data.\n";
    }
};
