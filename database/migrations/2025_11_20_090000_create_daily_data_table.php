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
        Schema::create('daily_data', function (Blueprint $table) {
            $table->id();
            $table->string('school');
            $table->string('class');
            $table->integer('duration')->default(25);
            $table->date('date');
            $table->string('day')->nullable();
            $table->time('time_jst')->nullable();
            $table->time('time_pht')->nullable();
            $table->integer('number_required')->default(1);

            // New / normalized fields referenced in models
            $table->enum('schedule_status', ['draft','tentative','finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->string('assigned_supervisor')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->enum('class_status', ['active','cancelled'])->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->unique(['school','class','date','time_jst']);
            $table->index('date');
            $table->index('schedule_status');
            $table->index('class_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_data');
    }
};
