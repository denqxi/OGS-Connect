<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('tutor_work_details', function (Blueprint $table) {
        $table->enum('status', ['pending', 'approved', 'reject'])->default('pending')->change();
    });
}

public function down()
{
    Schema::table('tutor_work_details', function (Blueprint $table) {
        $table->enum('status', ['pending', 'approved'])->default('pending')->change();
    });
}
};