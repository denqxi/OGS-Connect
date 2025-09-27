<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('tutorID');
            $table->string('role', 50)->nullable();
            $table->timestamps();

            $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
