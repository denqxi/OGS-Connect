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
        if (!Schema::hasTable('schedule_history')) {
            Schema::create('schedule_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->string('class_name');
            $table->string('school');
            $table->date('class_date');
            $table->time('class_time')->nullable();
            $table->enum('status', ['draft', 'tentative', 'finalized', 'cancelled', 'rescheduled'])->default('draft');
            $table->enum('action', ['created', 'updated', 'finalized', 'cancelled', 'rescheduled'])->default('created');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('reason')->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('daily_data')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['class_id', 'action']);
            $table->index(['status', 'created_at']);
            $table->index('performed_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_history');
    }
};
