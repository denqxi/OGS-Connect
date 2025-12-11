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
        // Table was already introduced earlier; guard to avoid duplicate table errors on re-run
        if (Schema::hasTable('security_questions')) {
            return;
        }

        Schema::create('security_questions', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // 'supervisor' or 'tutor'
            $table->string('user_id'); // supID or tutorID
            $table->string('question');
            $table->string('answer_hash');
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
