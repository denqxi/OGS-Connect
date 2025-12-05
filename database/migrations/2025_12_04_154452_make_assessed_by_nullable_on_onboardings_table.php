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
        Schema::table('onboardings', function (Blueprint $table) {
            // Revert to NOT NULL if rolling back
            if (Schema::hasColumn('onboardings', 'assessed_by')) {
                $table->unsignedBigInteger('assessed_by')->nullable(false)->change();
            }
        });
    }
};
