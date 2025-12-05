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
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            // Add foreign key constraint for assigned_supervisor
            $table->foreign('assigned_supervisor')
                  ->references('supervisor_id')
                  ->on('supervisor')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            $table->dropForeign(['assigned_supervisor']);
        });
    }
};
