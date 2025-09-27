<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id('availID');
            $table->unsignedBigInteger('tutorID');
            $table->unsignedBigInteger('timeslotID');
            $table->enum('availStatus', ['available', 'unavailable'])->default('available');
            $table->timestamps();

            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
            $table->foreign('timeslotID')->references('timeslotID')->on('time_slots')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
