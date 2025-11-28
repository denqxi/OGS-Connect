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
        Schema::table('supervisor', function (Blueprint $table) {
            $table->string('supID', 50)->unique()->nullable()->after('supervisor_id');
            $table->string('password', 255)->nullable()->after('supID');
            $table->string('remember_token', 100)->nullable()->after('password');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisor', function (Blueprint $table) {
            $table->dropColumn(['supID', 'password', 'remember_token', 'status']);
        });
    }
};

