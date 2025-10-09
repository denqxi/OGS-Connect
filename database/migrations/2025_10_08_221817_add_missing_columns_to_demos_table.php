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
        Schema::table('demos', function (Blueprint $table) {
            // Check if moved_to_onboarding_at exists, if not add it
            if (!Schema::hasColumn('demos', 'moved_to_onboarding_at')) {
                $table->timestamp('moved_to_onboarding_at')->nullable()->after('moved_to_training_at');
            }
            // Check if hired_at exists, if not add it
            if (!Schema::hasColumn('demos', 'hired_at')) {
                $table->timestamp('hired_at')->nullable()->after('moved_to_onboarding_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demos', function (Blueprint $table) {
            if (Schema::hasColumn('demos', 'hired_at')) {
                $table->dropColumn('hired_at');
            }
        });
    }
};
