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
        Schema::create('schedule_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id'); // Reference to daily_data.id
            $table->string('class_name'); // Store class name for history
            $table->string('school'); // Store school name
            $table->date('class_date'); // Store class date
            $table->time('class_time')->nullable(); // Store class time
            $table->enum('status', ['draft', 'tentative', 'finalized', 'cancelled', 'rescheduled']);
            $table->enum('action', ['created', 'updated', 'finalized', 'cancelled', 'rescheduled']);
            $table->unsignedBigInteger('performed_by')->nullable(); // User ID who performed action
            $table->text('reason')->nullable(); // Optional reason for changes
            $table->json('old_data')->nullable(); // Store previous state data
            $table->json('new_data')->nullable(); // Store new state data
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('class_id')->references('id')->on('daily_data')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['class_id', 'action']);
            $table->index(['status', 'created_at']);
            $table->index('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_history');
    }
};
