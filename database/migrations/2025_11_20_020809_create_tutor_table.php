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
        Schema::create('tutors', function (Blueprint $table) {
            $table->id('tutor_id');
            $table->unsignedBigInteger('applicant_id');
            $table->foreign('applicant_id')->references('applicant_id')->on('applicants')->onDelete('cascade');  
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')->references('account_id')->on('accounts')->onDelete('cascade');
            $table->datetime('hired_date_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
