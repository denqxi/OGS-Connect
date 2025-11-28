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
        Schema::create('tutor_accounts', function (Blueprint $table) {
            $table->id();
            // tutor_id is a formatted string (e.g., OGS-T0001)
            $table->string('tutor_id', 50)->index();
            $table->string('account_name')->nullable();
            $table->unsignedBigInteger('gls_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('username')->nullable();
            $table->string('screen_name')->nullable();
            $table->json('available_days')->nullable();
            $table->json('available_times')->nullable();
            $table->string('preferred_time_range')->nullable();
            $table->string('timezone')->nullable()->default('UTC');
            $table->time('restricted_start_time')->nullable();
            $table->time('restricted_end_time')->nullable();
            $table->text('company_notes')->nullable();
            $table->text('availability_notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            // No foreign key to tutor table since tutor uses custom PK string; index only
            $table->index(['tutor_id', 'account_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_accounts');
    }
};
