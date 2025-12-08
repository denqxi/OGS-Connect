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
        Schema::table('tutor_work_details', function (Blueprint $table) {
            // Add assignment_id to link work details to assigned_daily_data
            if (!Schema::hasColumn('tutor_work_details', 'assignment_id')) {
                $table->unsignedBigInteger('assignment_id')->nullable()->after('id');
                
                // Add foreign key
                $table->foreign('assignment_id')
                    ->references('id')
                    ->on('assigned_daily_data')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_work_details', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
    }
};
