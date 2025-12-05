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
        Schema::table('tutors', function (Blueprint $table) {
            if (Schema::hasColumns('tutors', ['full_name', 'phone_number', 'sex', 'date_of_birth', 'remember_token'])) {
                $table->dropColumn([
                    'full_name',
                    'phone_number',
                    'sex',
                    'date_of_birth',
                    'remember_token',
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors', 'full_name')) {
                $table->string('full_name', 200)->nullable()->after('password');
            }
            if (!Schema::hasColumn('tutors', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('tutors', 'sex')) {
                $table->enum('sex', ['male', 'female', 'other'])->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('tutors', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('sex');
            }
            if (!Schema::hasColumn('tutors', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('status');
            }
        });
    }
};
