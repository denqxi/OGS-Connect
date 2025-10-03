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
            $table->string('gls_id')->nullable()->after('account_name'); // GLS numeric ID (890, 17928, etc.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropColumn('gls_id');
        });
    }
};
