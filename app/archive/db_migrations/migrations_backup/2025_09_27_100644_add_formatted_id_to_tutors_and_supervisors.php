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
        // Add formatted_id to tutors table
        Schema::table('tutors', function (Blueprint $table) {
            $table->string('formatted_id', 20)->unique()->nullable()->after('tutorID');
        });

        // Add formatted_id to supervisors table  
        Schema::table('supervisors', function (Blueprint $table) {
            $table->string('formatted_id', 20)->unique()->nullable()->after('supID');
        });

        // Populate formatted IDs for existing records
        $this->populateFormattedIds();

        // Make formatted_id non-nullable after population
        Schema::table('tutors', function (Blueprint $table) {
            $table->string('formatted_id', 20)->nullable(false)->change();
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->string('formatted_id', 20)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn('formatted_id');
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropColumn('formatted_id');
        });
    }

    /**
     * Populate formatted IDs for existing records
     */
    private function populateFormattedIds(): void
    {
        // Update tutors with formatted IDs
        $tutors = DB::table('tutors')->orderBy('tutorID')->get();
        foreach ($tutors as $tutor) {
            $formattedId = 'OGS-T' . str_pad($tutor->tutorID, 4, '0', STR_PAD_LEFT);
            DB::table('tutors')
                ->where('tutorID', $tutor->tutorID)
                ->update(['formatted_id' => $formattedId]);
        }

        // Update supervisors with formatted IDs
        $supervisors = DB::table('supervisors')->orderBy('supID')->get();
        foreach ($supervisors as $supervisor) {
            $formattedId = 'OGS-S' . str_pad($supervisor->supID, 4, '0', STR_PAD_LEFT);
            DB::table('supervisors')
                ->where('supID', $supervisor->supID)
                ->update(['formatted_id' => $formattedId]);
        }
    }
};
