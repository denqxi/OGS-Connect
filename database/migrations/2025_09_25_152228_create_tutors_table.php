<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutors', function (Blueprint $table) {
            $table->id('tutorID');
            $table->string('applicantID', 50)->nullable();
            $table->string('email', 100)->unique();
            $table->string('tusername', 50)->unique();
            $table->string('tpassword', 255); // hashed password
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
