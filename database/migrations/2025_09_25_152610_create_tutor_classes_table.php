<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutor_classes', function (Blueprint $table) {
            $table->id('tutorClassID');
            $table->unsignedBigInteger('tutorID');
            $table->unsignedBigInteger('classID');
            $table->timestamps();

            $table->foreign('tutorID')->references('tutorID')->on('tutors')->onDelete('cascade');
            $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_classes');
    }
};
