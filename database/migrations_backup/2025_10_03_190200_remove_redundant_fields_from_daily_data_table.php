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
            // Remove redundant fields that can be derived from other fields
            $table->dropColumn('time_pht'); // day column doesn't exist in current structure
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            // Add back the field if needed to rollback
            $table->string('time_pht')->nullable();
        });
    }
};
