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
            if (!Schema::hasColumn('daily_data', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('finalized_at');
                $table->foreign('finalized_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_data', function (Blueprint $table) {
            if (Schema::hasColumn('daily_data', 'finalized_by')) {
                $table->dropForeign(['finalized_by']);
                $table->dropColumn('finalized_by');
            }
        });
    }
};
