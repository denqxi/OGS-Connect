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
        Schema::table('archive', function (Blueprint $table) {
            $table->string('category')->nullable()->after('archive_by');
            $table->string('status')->nullable()->after('category');
            $table->json('payload')->nullable()->after('status');
            // keep existing notes/archive_date_time columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archive', function (Blueprint $table) {
            $table->dropColumn(['category', 'status', 'payload']);
        });
    }
};
