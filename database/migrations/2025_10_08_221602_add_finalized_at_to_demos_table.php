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
            $table->timestamp('finalized_at')->nullable()->after('moved_to_demo_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demos', function (Blueprint $table) {
            $table->dropColumn('finalized_at');
        });
    }
};
