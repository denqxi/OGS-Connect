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
            $table->enum('user_type', ['tutor', 'supervisor']);
            $table->string('user_id'); // Can be tutorID or supID
            $table->text('question');
            $table->string('answer_hash'); // Hashed answer
            $table->timestamps();
            
            // Indexes
            $table->index(['user_type', 'user_id']);
            $table->unique(['user_type', 'user_id']); // One security question per user
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
