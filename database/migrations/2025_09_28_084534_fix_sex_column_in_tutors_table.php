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
        Schema::table('tutors', function (Blueprint $table) {
            // Drop the existing sex column
            $table->dropColumn('sex');
        });

        Schema::table('tutors', function (Blueprint $table) {
            // Add the new sex column with M/F enum
            $table->enum('sex', ['M', 'F'])->default('M')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn('sex');
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->enum('sex', ['male', 'female'])->nullable()->after('status');
        });
    }
};
