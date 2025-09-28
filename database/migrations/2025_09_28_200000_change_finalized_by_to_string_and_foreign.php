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
            // Change finalized_by to string to match supervisors.supID
            $table->string('finalized_by', 20)->nullable()->change();
            // Add new foreign key to supervisors.supID
            $table->foreign('finalized_by')->references('supID')->on('supervisors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            // Optionally revert to unsignedBigInteger
            // $table->unsignedBigInteger('finalized_by')->nullable()->change();
        });
    }
};
