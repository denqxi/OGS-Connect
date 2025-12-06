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
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            // Change assigned_supervisor from varchar to bigint unsigned to match supervisor_id
            $table->unsignedBigInteger('assigned_supervisor')->nullable()->change();
            
            // Add foreign key constraint
            $table->foreign('assigned_supervisor')
                  ->references('supervisor_id')
                  ->on('supervisor')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_daily_data', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['assigned_supervisor']);
            
            // Change back to varchar
            $table->string('assigned_supervisor', 255)->nullable()->change();
        });
    }
};
