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
        Schema::table('tutor', function (Blueprint $table) {
            $table->string('tutorID', 50)->unique()->nullable()->after('tutor_id');
            $table->string('username', 50)->unique()->nullable()->after('tutorID');
            $table->string('email', 100)->unique()->nullable()->after('username');
            $table->string('password', 255)->nullable()->after('email');
            $table->string('full_name', 200)->nullable()->after('password');
            $table->string('phone_number', 20)->nullable()->after('full_name');
            $table->enum('sex', ['male', 'female', 'other'])->nullable()->after('phone_number');
            $table->date('date_of_birth')->nullable()->after('sex');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('date_of_birth');
            $table->string('remember_token', 100)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor', function (Blueprint $table) {
            $table->dropColumn([
                'tutorID',
                'username',
                'email',
                'password',
                'full_name',
                'phone_number',
                'sex',
                'date_of_birth',
                'status',
                'remember_token'
            ]);
        });
    }
};

