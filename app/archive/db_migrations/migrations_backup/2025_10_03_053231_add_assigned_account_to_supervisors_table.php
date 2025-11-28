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
        Schema::table('supervisors', function (Blueprint $table) {
            $table->enum('assigned_account', ['GLS', 'Tutlo', 'Babilala', 'Talk915'])->nullable()->after('sconNum');
            $table->string('srole', 100)->default('Supervisor')->after('assigned_account');
            $table->string('saddress', 500)->nullable()->after('srole');
            $table->string('steams', 100)->nullable()->after('saddress');
            $table->string('sshift', 50)->nullable()->after('steams');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('sshift');
            $table->string('susername', 50)->nullable()->after('status');
            
            // Add index for faster lookups by account
            $table->index('assigned_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropIndex(['assigned_account']);
            $table->dropColumn([
                'assigned_account',
                'srole',
                'saddress', 
                'steams',
                'sshift',
                'status',
                'susername'
            ]);
        });
    }
};