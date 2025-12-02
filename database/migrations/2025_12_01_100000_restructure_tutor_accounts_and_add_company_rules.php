<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration comprehensively restructures the tutor_accounts and accounts tables:
     * 1. Adds company rules (operating hours) to accounts table
     * 2. Creates foreign key relationship from tutor_accounts to accounts
     * 3. Removes redundant/duplicate columns from tutor_accounts
     * 4. Consolidates notes fields
     * 5. Moves company-specific rules from tutor_accounts to accounts table
     */
    public function up(): void
    {
        // STEP 1: Add company-specific rules to accounts table
        Schema::table('accounts', function (Blueprint $table) {
            $table->time('operating_start_time')->nullable()->after('industry')
                ->comment('Company operating hours start time (e.g., 07:00:00 for GLS)');
            $table->time('operating_end_time')->nullable()->after('operating_start_time')
                ->comment('Company operating hours end time (e.g., 15:30:00 for GLS)');
            $table->text('company_rules')->nullable()->after('operating_end_time')
                ->comment('Company-specific rules and restrictions');
        });

        // STEP 2: Populate company rules from existing data
        DB::table('accounts')->where('account_name', 'GLS')->update([
            'operating_start_time' => '07:00:00',
            'operating_end_time' => '15:30:00',
            'company_rules' => 'GLS operates from 7:00 AM to 3:30 PM only. No weekend availability.',
        ]);

        DB::table('accounts')->where('account_name', 'Babilala')->update([
            'operating_start_time' => '20:00:00',
            'operating_end_time' => '22:00:00',
            'company_rules' => 'Babilala operates from 8:00 PM to 10:00 PM only. Evening hours only.',
        ]);

        DB::table('accounts')->whereIn('account_name', ['Tutlo', 'Talk915'])->update([
            'operating_start_time' => null,
            'operating_end_time' => null,
            'company_rules' => 'Open hours - no time restrictions.',
        ]);

        // STEP 3: Add foreign key column and index
        Schema::table('tutor_accounts', function (Blueprint $table) {
            // Add account_id column that will reference accounts table
            $table->unsignedBigInteger('account_id')->nullable()->after('tutor_id');
            $table->index('account_id');
        });

        // STEP 4: Populate account_id from account_name
        $accounts = DB::table('accounts')->select('account_id', 'account_name')->get();
        foreach ($accounts as $account) {
            DB::table('tutor_accounts')
                ->where('account_name', $account->account_name)
                ->update(['account_id' => $account->account_id]);
        }

        // STEP 5: Consolidate notes fields - merge company_notes and availability_notes
        DB::statement('
            UPDATE tutor_accounts 
            SET availability_notes = CASE
                WHEN company_notes IS NOT NULL AND availability_notes IS NOT NULL 
                    THEN CONCAT(company_notes, " | ", availability_notes)
                WHEN company_notes IS NOT NULL 
                    THEN company_notes
                ELSE availability_notes
            END
        ');

        // STEP 6: Drop columns that are now redundant
        Schema::table('tutor_accounts', function (Blueprint $table) {
            // Drop company-specific rules (now in accounts table)
            $table->dropColumn([
                'restricted_start_time',    // Moved to accounts.operating_start_time
                'restricted_end_time',      // Moved to accounts.operating_end_time
                'company_notes',            // Merged into availability_notes (renamed to notes below)
                'preferred_time_range',     // Not consistently used, can be derived from available_times
            ]);
        });

        // STEP 7: Rename availability_notes to just 'notes' for simplicity
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->renameColumn('availability_notes', 'notes');
        });

        // STEP 8: Add foreign key constraint
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->foreign('account_id')
                ->references('account_id')
                ->on('accounts')
                ->onDelete('cascade');
        });

        // STEP 9: Update composite index to use account_id instead of account_name
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropIndex(['tutor_id', 'account_name']);
            $table->unique(['tutor_id', 'account_id'], 'tutor_accounts_tutor_account_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse STEP 9: Restore old index
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropUnique('tutor_accounts_tutor_account_unique');
            $table->index(['tutor_id', 'account_name']);
        });

        // Reverse STEP 8: Drop foreign key
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        // Reverse STEP 7: Rename notes back to availability_notes
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->renameColumn('notes', 'availability_notes');
        });

        // Reverse STEP 6: Restore dropped columns
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->time('restricted_start_time')->nullable()->after('timezone');
            $table->time('restricted_end_time')->nullable()->after('restricted_start_time');
            $table->text('company_notes')->nullable()->after('restricted_end_time');
            $table->string('preferred_time_range')->nullable()->after('available_times');
        });

        // Reverse STEP 5: Split notes back (best effort - data loss may occur)
        // Note: This is a best-effort reversal, original split cannot be perfectly restored

        // Reverse STEP 3-4: Drop account_id column
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropIndex(['account_id']);
            $table->dropColumn('account_id');
        });

        // Reverse STEP 1: Remove company rules from accounts table
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'operating_start_time',
                'operating_end_time',
                'company_rules',
            ]);
        });
    }
};
