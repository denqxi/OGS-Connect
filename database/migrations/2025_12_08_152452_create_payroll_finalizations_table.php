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
        Schema::create('payroll_finalizations', function (Blueprint $table) {
            $table->id('finalization_id');
            $table->unsignedBigInteger('tutor_id');
            $table->string('pay_period'); // e.g., "2025-12 (1-15)"
            $table->decimal('total_amount', 12, 2);
            $table->integer('work_details_count')->default(0);
            $table->enum('status', ['draft', 'sent', 'processed', 'locked'])->default('draft');
            $table->dateTime('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->foreign('tutor_id')->references('tutor_id')->on('tutors')->onDelete('cascade');
            $table->unique(['tutor_id', 'pay_period']); // One finalization per tutor per period
            $table->index('pay_period');
            $table->index('status');
            $table->index('finalized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_finalizations');
    }
};
