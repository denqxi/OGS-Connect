<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('tutor_work_details', function (Blueprint $table) {
        $table->id();

        // FK references tutorID (string)
        $table->string('tutor_id'); // stores OGS-T0001

        $table->string('day'); // e.g., Monday
        $table->time('start_time')->nullable();
        $table->time('end_time')->nullable();
        $table->integer('duration_minutes')->nullable();

        $table->timestamps();

        // foreign key referencing tutorID
        $table->foreign('tutor_id')
            ->references('tutorID')
            ->on('tutors')
            ->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_work_details');
    }
};
