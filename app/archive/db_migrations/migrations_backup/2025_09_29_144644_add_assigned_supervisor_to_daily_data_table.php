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
        Schema::table('daily_data', function (Blueprint $table) {
            $table->string('assigned_supervisor', 20)->nullable()->after('finalized_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_supervisor');
            
            // Note: Foreign key constraint removed for simplicity
            // The assigned_supervisor field stores the supervisor's formatted_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            $table->dropColumn(['assigned_supervisor', 'assigned_at']);
        });
    }
};