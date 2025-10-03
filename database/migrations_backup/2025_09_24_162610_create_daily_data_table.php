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
            $table->string('day');
            $table->string('time_jst');
            $table->string('time_pht');
            $table->integer('number_required');
            $table->timestamps();

            $table->unique(['school','class','date','time_jst']); // prevent duplicates
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
