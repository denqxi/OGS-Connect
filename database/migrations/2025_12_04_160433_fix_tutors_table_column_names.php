<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tusername to username
        if (Schema::hasColumn('tutors', 'tusername') && !Schema::hasColumn('tutors', 'username')) {
            DB::statement('ALTER TABLE tutors CHANGE tusername username VARCHAR(50)');
        }
        
        // Rename tpassword to password
        if (Schema::hasColumn('tutors', 'tpassword') && !Schema::hasColumn('tutors', 'password')) {
            DB::statement('ALTER TABLE tutors CHANGE tpassword password VARCHAR(255)');
        }
        
        // Also rename hired_date_time to hire_date_time for consistency
        if (Schema::hasColumn('tutors', 'hired_date_time') && !Schema::hasColumn('tutors', 'hire_date_time')) {
            DB::statement('ALTER TABLE tutors CHANGE hired_date_time hire_date_time DATETIME');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the renames
        if (Schema::hasColumn('tutors', 'username') && !Schema::hasColumn('tutors', 'tusername')) {
            DB::statement('ALTER TABLE tutors CHANGE username tusername VARCHAR(50)');
        }
        
        if (Schema::hasColumn('tutors', 'password') && !Schema::hasColumn('tutors', 'tpassword')) {
            DB::statement('ALTER TABLE tutors CHANGE password tpassword VARCHAR(255)');
        }
        
        if (Schema::hasColumn('tutors', 'hire_date_time') && !Schema::hasColumn('tutors', 'hired_date_time')) {
            DB::statement('ALTER TABLE tutors CHANGE hire_date_time hired_date_time DATE');
        }
    }
};
