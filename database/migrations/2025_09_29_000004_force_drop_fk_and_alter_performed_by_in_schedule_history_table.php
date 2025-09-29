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
        // Drop the foreign key constraint by name
        DB::statement('ALTER TABLE schedule_history DROP FOREIGN KEY schedule_history_performed_by_foreign');
        // Now change the column type
        Schema::table('schedule_history', function (Blueprint $table) {
            $table->string('performed_by', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_history', function (Blueprint $table) {
            $table->unsignedBigInteger('performed_by')->nullable()->change();
            // Optionally, re-add the foreign key if needed
            // $table->foreign('performed_by')->references('id')->on('supervisors')->onDelete('set null');
        });
    }
};
