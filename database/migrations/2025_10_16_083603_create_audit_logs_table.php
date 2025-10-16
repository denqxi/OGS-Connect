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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50); // login, password_reset, schedule_change, etc.
            $table->string('user_type', 20); // supervisor, tutor
            $table->string('user_id', 50)->nullable(); // supID for supervisors, tutorID for tutors
            $table->string('user_email', 255)->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('action', 100); // specific action performed
            $table->text('description')->nullable(); // detailed description
            $table->json('metadata')->nullable(); // additional data (old/new values, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->boolean('is_important')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['event_type', 'created_at']);
            $table->index(['user_type', 'user_id']);
            $table->index(['is_important', 'created_at']);
            $table->index(['severity', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
