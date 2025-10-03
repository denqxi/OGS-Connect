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
            $table->unsignedBigInteger('tutor_id');
            $table->text('address')->nullable();
            $table->text('esl_experience')->nullable();
            $table->enum('work_setup', ['remote', 'onsite', 'hybrid'])->nullable();
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
            
            // Foreign key
            $table->foreign('tutor_id')->references('tutorID')->on('tutors')->onDelete('cascade');
            $table->unique('tutor_id'); // One detail record per tutor
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
