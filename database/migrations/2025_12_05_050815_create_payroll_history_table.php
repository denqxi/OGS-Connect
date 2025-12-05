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
        Schema::create('payroll_history', function (Blueprint $table) {
            $table->id('payroll_history_id');
            $table->unsignedBigInteger('tutor_id');
            $table->foreign('tutor_id')->references('tutor_id')->on('tutors')->onDelete('cascade');
            $table->string('pay_period'); // e.g., "2025-01", "December 2025"
            $table->enum('submission_type', ['email', 'pdf', 'print']); // How it was sent/saved
            $table->enum('status', ['sent', 'pending', 'failed', 'draft'])->default('pending');
            $table->string('recipient_email')->nullable(); // Email address if sent via email
            $table->text('notes')->nullable(); // Any additional notes
            $table->timestamp('submitted_at')->useCurrent(); // When the action was taken
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_history');
    }
};
