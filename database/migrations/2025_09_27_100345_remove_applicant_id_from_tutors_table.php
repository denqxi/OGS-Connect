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
        Schema::table('tutors', function (Blueprint $table) {
            // Remove unused applicantID field
            $table->dropColumn('applicantID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            // Restore the field in case of rollback
            $table->string('applicantID', 50)->nullable()->after('tutorID');
        });
    }
};
