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
            $table->unsignedBigInteger('tutor_id');
            $table->string('account_name'); // 'GLS', 'Babilala', etc.
            $table->string('gls_id')->nullable(); // GLS numeric ID
            $table->string('account_number')->nullable(); // Account number for all account types
            $table->string('username')->nullable(); // Username for the account
            $table->string('screen_name')->nullable(); // Screen name for the account
            
            // Account-specific availability
            $table->json('available_days')->nullable(); // JSON array of days for this account
            $table->json('available_times')->nullable(); // JSON object with day-specific times
            $table->enum('preferred_time_range', ['morning', 'afternoon', 'evening', 'flexible'])->default('flexible');
            $table->string('timezone', 10)->default('UTC');
            $table->text('availability_notes')->nullable();
            
            // Company time restrictions
            $table->time('restricted_start_time')->nullable();
            $table->time('restricted_end_time')->nullable();
            $table->text('company_notes')->nullable();
            
            // Status for this account
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tutor_id', 'account_name']);
            $table->unique(['tutor_id', 'account_name']); // One record per tutor per account
            
            // Foreign key
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
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
