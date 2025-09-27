<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id('classID');
            $table->string('schoolName', 100);
            $table->string('classCode', 50)->nullable();
            $table->integer('duration')->nullable();
            $table->date('date');
            $table->string('day', 20)->nullable();
            $table->time('Jtime')->nullable();
            $table->time('Ptime')->nullable();
            $table->integer('slots')->default(0);
            $table->unsignedBigInteger('supID')->nullable();
            $table->unsignedBigInteger('timeslotID')->nullable();
            $table->timestamps();

            $table->foreign('supID')->references('supID')->on('supervisors')->onDelete('set null');
            $table->foreign('timeslotID')->references('timeslotID')->on('time_slots')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
