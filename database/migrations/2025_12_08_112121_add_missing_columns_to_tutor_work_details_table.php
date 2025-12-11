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
        Schema::table('tutor_work_details', function (Blueprint $table) {
            // Add missing columns for work details
            $table->string('work_type')->nullable()->after('schedule_daily_data_id');
            $table->decimal('rate_per_hour', 10, 2)->default(0)->after('duration_minutes');
            $table->decimal('rate_per_class', 10, 2)->default(0)->after('rate_per_hour');
            $table->string('proof_image')->nullable()->after('screenshot');
            $table->text('note')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_work_details', function (Blueprint $table) {
            $table->dropColumn(['work_type', 'rate_per_hour', 'rate_per_class', 'proof_image', 'note']);
        });
    }
};
