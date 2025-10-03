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
            $table->string('account_number')->nullable()->after('gls_id'); // Account number for all account types
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropColumn('account_number');
        });
    }
};