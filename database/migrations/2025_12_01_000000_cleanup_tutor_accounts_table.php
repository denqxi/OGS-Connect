<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration cleans up the tutor_accounts table by removing account-specific
     * columns that are not consistently used. The tutor_accounts table should only
     * contain availability information and reference the main tutor table for all
     * other tutor details.
     */
    public function up(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            // Drop account-specific columns that are not needed
            // These values should come from the tutor table or applicant relationship
            $table->dropColumn([
                'gls_id',           // Account-specific IDs not needed
                'account_number',   // Account-specific IDs not needed
                'username',         // Should use tutor.username
                'screen_name',      // Account-specific field not consistently used
                'status',           // Status comes from tutor table
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            // Restore the removed columns
            $table->unsignedBigInteger('gls_id')->nullable()->after('account_name');
            $table->string('account_number')->nullable()->after('gls_id');
            $table->string('username')->nullable()->after('account_number');
            $table->string('screen_name')->nullable()->after('username');
            $table->string('status')->default('active')->after('availability_notes');
        });
    }
};
