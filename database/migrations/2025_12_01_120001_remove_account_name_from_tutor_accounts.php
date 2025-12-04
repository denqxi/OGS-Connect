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
     * Remove account_name from tutor_accounts table since we already have
     * account_id foreign key that references accounts table.
     */
    public function up(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->dropColumn('account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_accounts', function (Blueprint $table) {
            $table->string('account_name', 255)->nullable()->after('account_id');
        });
        
        // Repopulate account_name from account_id
        DB::statement('
            UPDATE tutor_accounts ta
            INNER JOIN accounts a ON ta.account_id = a.account_id
            SET ta.account_name = a.account_name
        ');
    }
};
