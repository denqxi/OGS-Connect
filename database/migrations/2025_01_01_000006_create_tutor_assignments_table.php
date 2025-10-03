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
        Schema::create('tutor_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_data_id');
            $table->unsignedBigInteger('tutor_id');
            $table->boolean('is_backup')->default(false);
            $table->boolean('was_promoted_from_backup')->default(false);
            $table->string('replaced_tutor_name')->nullable();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->decimal('similarity_score', 5, 4)->nullable();
            $table->enum('status', ['assigned', 'confirmed', 'cancelled'])->default('assigned');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('daily_data_id')->references('id')->on('daily_data')->onDelete('cascade');
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
            
            // Prevent duplicate assignments (same tutor assigned to same class)
            $table->unique(['daily_data_id', 'tutor_id']);
            
            // Indexes for better performance
            $table->index('daily_data_id');
            $table->index('tutor_id');
            $table->index('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_assignments');
    }
};
