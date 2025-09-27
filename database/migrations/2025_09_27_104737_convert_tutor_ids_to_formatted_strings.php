<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration converts tutorID and supID from numeric to formatted strings
        // This is a DESTRUCTIVE operation - make sure you have a backup!
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Step 1: Create mapping of old numeric IDs to new formatted IDs
        $tutorMapping = [];
        $supervisorMapping = [];

        // Get existing tutors and create formatted IDs
        $tutors = DB::table('tutors')->orderBy('tutorID', 'asc')->get();
        foreach ($tutors as $index => $tutor) {
            $formattedId = 'OGS-T' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $tutorMapping[$tutor->tutorID] = $formattedId;
        }

        // Get existing supervisors and create formatted IDs  
        $supervisors = DB::table('supervisors')->orderBy('supID', 'asc')->get();
        foreach ($supervisors as $index => $supervisor) {
            $formattedId = 'OGS-S' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $supervisorMapping[$supervisor->supID] = $formattedId;
        }

        // Step 2: Drop all foreign key constraints that reference tutorID/supID
        $this->dropForeignKeyIfExists('tutor_accounts', 'tutor_accounts_tutor_id_foreign');
        $this->dropForeignKeyIfExists('tutor_assignments', 'tutor_assignments_tutor_id_foreign');
        $this->dropForeignKeyIfExists('schedules', 'schedules_tutorid_foreign');
        $this->dropForeignKeyIfExists('availabilities', 'availabilities_tutorid_foreign');
        $this->dropForeignKeyIfExists('tutor_classes', 'tutor_classes_tutorid_foreign');
        $this->dropForeignKeyIfExists('classes', 'classes_supid_foreign');

        // Step 3: Convert column types to VARCHAR(20) FIRST
        
        // Convert foreign key columns first
        DB::statement('ALTER TABLE tutor_accounts MODIFY COLUMN tutor_id VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE tutor_assignments MODIFY COLUMN tutor_id VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE schedules MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE availabilities MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE tutor_classes MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE classes MODIFY COLUMN supID VARCHAR(20) NULL');

        // Convert primary key columns
        DB::statement('ALTER TABLE tutors MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE supervisors MODIFY COLUMN supID VARCHAR(20) NOT NULL');

        // Step 4: Now update all the ID values
        
        // Update tutors table first
        foreach ($tutorMapping as $oldId => $newId) {
            DB::table('tutors')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
        }

        // Update supervisors table 
        foreach ($supervisorMapping as $oldId => $newId) {
            DB::table('supervisors')->where('supID', $oldId)->update(['supID' => $newId]);
        }

        // Update foreign key references
        foreach ($tutorMapping as $oldId => $newId) {
            DB::table('tutor_accounts')->where('tutor_id', $oldId)->update(['tutor_id' => $newId]);
            DB::table('tutor_assignments')->where('tutor_id', $oldId)->update(['tutor_id' => $newId]);
            DB::table('schedules')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
            DB::table('availabilities')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
            DB::table('tutor_classes')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
        }

        foreach ($supervisorMapping as $oldId => $newId) {
            DB::table('classes')->where('supID', $oldId)->update(['supID' => $newId]);
        }

        // Step 5: Re-add foreign key constraints
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('availabilities', function (Blueprint $table) {
            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('tutor_classes', function (Blueprint $table) {
            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('supID')->references('supID')->on('supervisors')->onDelete('set null');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "Successfully converted:\n";
        echo "- " . count($tutorMapping) . " tutor IDs to formatted strings\n";
        echo "- " . count($supervisorMapping) . " supervisor IDs to formatted strings\n";
        echo "- Updated all foreign key references\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \Exception('This migration cannot be safely rolled back. Restore from backup if needed.');
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        try {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$foreignKey}");
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }
    }
};
