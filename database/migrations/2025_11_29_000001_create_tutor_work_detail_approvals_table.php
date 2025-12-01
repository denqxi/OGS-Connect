<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_work_detail_approvals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('work_detail_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('work_detail_id')
                ->references('id')->on('tutor_work_details')
                ->onDelete('cascade');

            $table->foreign('supervisor_id')
                ->references('supervisor_id')->on('supervisors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tutor_work_detail_approvals', function (Blueprint $table) {
            $table->dropForeign(['work_detail_id']);
            $table->dropForeign(['supervisor_id']);
        });
        Schema::dropIfExists('tutor_work_detail_approvals');
    }
};
