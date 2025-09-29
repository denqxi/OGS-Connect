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
        // Step 1: Convert tutors table
        $this->convertTutorsTable();
        
        // Step 2: Convert supervisors table
        $this->convertSupervisorsTable();
        
        // Step 3: Update foreign key references
        $this->updateForeignKeyReferences();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \Exception('This migration cannot be rolled back.');
    }

    private function convertTutorsTable(): void
    {
        // Get all tutors and create mapping
        $tutors = DB::table('tutors')->orderBy('tutorID')->get();
        $tutorMapping = [];
        
        foreach ($tutors as $index => $tutor) {
            $formattedId = 'OGS-T' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $tutorMapping[$tutor->tutorID] = $formattedId;
        }

        // Drop foreign key constraints that reference tutorID
        $this->dropForeignKeyIfExists('tutor_accounts', 'tutor_accounts_tutor_id_foreign');
        $this->dropForeignKeyIfExists('tutor_assignments', 'tutor_assignments_tutor_id_foreign');
        $this->dropForeignKeyIfExists('availabilities', 'availabilities_tutorid_foreign');

        // Change tutorID column type to VARCHAR
        DB::statement('ALTER TABLE tutors MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE tutor_accounts MODIFY COLUMN tutor_id VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE tutor_assignments MODIFY COLUMN tutor_id VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE availabilities MODIFY COLUMN tutorID VARCHAR(20) NOT NULL');

        // Update tutor IDs
        foreach ($tutorMapping as $oldId => $newId) {
            DB::table('tutors')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
            DB::table('tutor_accounts')->where('tutor_id', $oldId)->update(['tutor_id' => $newId]);
            DB::table('tutor_assignments')->where('tutor_id', $oldId)->update(['tutor_id' => $newId]);
            DB::table('availabilities')->where('tutorID', $oldId)->update(['tutorID' => $newId]);
        }

        // Remove the formatted_id column since we're using the primary key directly
        if (Schema::hasColumn('tutors', 'formatted_id')) {
            Schema::table('tutors', function (Blueprint $table) {
                $table->dropColumn('formatted_id');
            });
        }

        echo "Converted " . count($tutorMapping) . " tutor IDs to formatted strings\n";
    }

    private function convertSupervisorsTable(): void
    {
        // Get all supervisors and create mapping
        $supervisors = DB::table('supervisors')->orderBy('supID')->get();
        $supervisorMapping = [];
        
        foreach ($supervisors as $index => $supervisor) {
            $formattedId = 'OGS-S' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $supervisorMapping[$supervisor->supID] = $formattedId;
        }

        // Drop foreign key constraints that reference supID
        $this->dropForeignKeyIfExists('daily_data', 'daily_data_finalized_by_foreign');
        $this->dropForeignKeyIfExists('daily_data', 'daily_data_assigned_supervisor_foreign');
        $this->dropForeignKeyIfExists('schedule_history', 'schedule_history_performed_by_foreign');

        // Change supID column type to VARCHAR
        DB::statement('ALTER TABLE supervisors MODIFY COLUMN supID VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE daily_data MODIFY COLUMN finalized_by VARCHAR(20) NULL');
        DB::statement('ALTER TABLE daily_data MODIFY COLUMN assigned_supervisor VARCHAR(20) NULL');
        DB::statement('ALTER TABLE schedule_history MODIFY COLUMN performed_by VARCHAR(20) NULL');

        // Update supervisor IDs
        foreach ($supervisorMapping as $oldId => $newId) {
            DB::table('supervisors')->where('supID', $oldId)->update(['supID' => $newId]);
            DB::table('daily_data')->where('finalized_by', $oldId)->update(['finalized_by' => $newId]);
            DB::table('daily_data')->where('assigned_supervisor', $oldId)->update(['assigned_supervisor' => $newId]);
            DB::table('schedule_history')->where('performed_by', $oldId)->update(['performed_by' => $newId]);
        }

        // Remove the formatted_id column since we're using the primary key directly
        if (Schema::hasColumn('supervisors', 'formatted_id')) {
            Schema::table('supervisors', function (Blueprint $table) {
                $table->dropColumn('formatted_id');
            });
        }

        echo "Converted " . count($supervisorMapping) . " supervisor IDs to formatted strings\n";
    }

    private function updateForeignKeyReferences(): void
    {
        // Re-add foreign key constraints
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('availabilities', function (Blueprint $table) {
            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
        });

        Schema::table('daily_data', function (Blueprint $table) {
            $table->foreign('finalized_by')->references('supID')->on('supervisors')->nullOnDelete();
            $table->foreign('assigned_supervisor')->references('supID')->on('supervisors')->nullOnDelete();
        });

        Schema::table('schedule_history', function (Blueprint $table) {
            $table->foreign('performed_by')->references('supID')->on('supervisors')->nullOnDelete();
        });

        echo "Re-added foreign key constraints\n";
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
