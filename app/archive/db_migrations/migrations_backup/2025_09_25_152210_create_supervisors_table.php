<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id('supID');
            $table->string('sfname', 50);
            $table->string('smname', 50)->nullable();
            $table->string('slname', 50);
            $table->string('semail', 100)->unique();
            $table->string('sconNum', 20)->nullable();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};
