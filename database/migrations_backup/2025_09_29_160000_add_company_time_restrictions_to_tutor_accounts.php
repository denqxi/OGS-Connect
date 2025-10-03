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
        Schema::table('tutor_accounts', function (Blueprint $table) {
            // Add company-specific time restrictions
            $table->time('restricted_start_time')->nullable()->after('timezone');
            $table->time('restricted_end_time')->nullable()->after('restricted_start_time');
            $table->text('company_notes')->nullable()->after('restricted_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropColumn(['restricted_start_time', 'restricted_end_time', 'company_notes']);
        });
    }
};
