<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove unused columns from tutor table:
     * - full_name (can be derived from applicant table)
     * - phone_number (available in applicant table)
     * - sex (available in applicant table)
     * - date_of_birth (available in applicant table)
     * - remember_token (not used in current authentication)
     */
    public function up(): void
    {
        Schema::table('tutor', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'phone_number',
                'sex',
                'date_of_birth',
                'remember_token',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor', function (Blueprint $table) {
            $table->string('full_name', 200)->nullable()->after('password');
            $table->string('phone_number', 20)->nullable()->after('full_name');
            $table->enum('sex', ['male', 'female', 'other'])->nullable()->after('phone_number');
            $table->date('date_of_birth')->nullable()->after('sex');
            $table->string('remember_token', 100)->nullable()->after('status');
        });
    }
};
