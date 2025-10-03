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
        Schema::table('daily_data', function (Blueprint $table) {
            $table->enum('schedule_status', ['draft', 'partial', 'final'])->default('draft')->after('number_required');
            $table->timestamp('finalized_at')->nullable()->after('schedule_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            $table->dropColumn(['schedule_status', 'finalized_at']);
        });
    }
};
