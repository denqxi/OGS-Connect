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
            $table->unsignedBigInteger('class_id');
            $table->string('class_name');
            $table->string('school');
            $table->date('class_date');
            $table->time('class_time');
            $table->enum('status', ['draft', 'tentative', 'finalized', 'cancelled'])->default('draft');
            $table->string('action'); // 'created', 'updated', 'cancelled', 'finalized', etc.
            $table->string('performed_by')->nullable(); // User ID who performed the action
            $table->text('reason')->nullable(); // Reason for the action
            $table->json('old_data')->nullable(); // Previous data before change
            $table->json('new_data')->nullable(); // New data after change
            $table->timestamps();
            
            // Indexes
            $table->index('class_id');
            $table->index('class_date');
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
