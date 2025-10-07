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
            $table->time('time_pht')->nullable()->after('time_jst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            $table->dropColumn('time_pht');
        });
    }
};
