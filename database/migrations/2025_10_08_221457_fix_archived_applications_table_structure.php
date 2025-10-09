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
        Schema::table('archived_applications', function (Blueprint $table) {
            // Remove the redundant 'status' field since 'final_status' is sufficient
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archived_applications', function (Blueprint $table) {
            // Add back the status field
            $table->string('status')->after('interview_time');
        });
    }
};