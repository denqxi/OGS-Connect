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
        Schema::table('onboardings', function (Blueprint $table) {
            // Make assessed_by nullable if it exists
            if (Schema::hasColumn('onboardings', 'assessed_by')) {
                $table->unsignedBigInteger('assessed_by')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do nothing on rollback - keep the column nullable
        // to avoid issues with existing NULL values
    }
};
