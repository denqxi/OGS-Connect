<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Add composite unique index
            $table->unique(
                ['date', 'school', 'class', 'time_jst'],
                'unique_schedule'
            );
        });
    }

    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Drop the unique index if rolled back
            $table->dropUnique('unique_schedule');
        });
    }
};
