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
        Schema::create('security_questions', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // 'tutor' or 'supervisor'
            $table->string('user_id'); // tutorID or supID
            $table->string('question');
            $table->string('answer_hash'); // Hashed answer for security
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['user_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_questions');
    }
};