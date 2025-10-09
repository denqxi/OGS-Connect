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
        Schema::table('archived_applications', function (Blueprint $table) {
            $table->string('assigned_account')->nullable()->after('final_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archived_applications', function (Blueprint $table) {
            $table->dropColumn('assigned_account');
        });
    }
};
