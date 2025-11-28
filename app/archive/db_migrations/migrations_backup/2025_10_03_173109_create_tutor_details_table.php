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
        Schema::create('tutor_details', function (Blueprint $table) {
            $table->id();
            $table->string('tutor_id'); // References tutors.tutorID
            $table->text('address')->nullable();
            $table->string('esl_experience')->nullable(); // e.g., "2 years", "5 years"
            $table->enum('work_setup', ['WFH', 'WAS', 'Hybrid'])->nullable(); // Work From Home, Work At Site, Hybrid
            $table->date('first_day_teaching')->nullable();
            $table->enum('educational_attainment', [
                'High School',
                'Associate Degree',
                'Bachelors Degree',
                'Masters Degree',
                'Doctorate',
                'Other'
            ])->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('tutor_id');
            $table->unique('tutor_id'); // One details record per tutor
            
            // Foreign key
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_details');
    }
};