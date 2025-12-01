<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop existing foreign key if present, alter column to nullable, then re-add FK
        try {
            Schema::table('tutor_work_detail_approvals', function (Blueprint $table) {
                $table->dropForeign(['supervisor_id']);
            });
        } catch (\Exception $e) {
            // ignore if FK does not exist
        }

        // Use raw SQL for altering column to avoid requiring doctrine/dbal
        DB::statement('ALTER TABLE `tutor_work_detail_approvals` MODIFY `supervisor_id` BIGINT UNSIGNED NULL');

        Schema::table('tutor_work_detail_approvals', function (Blueprint $table) {
            $table->foreign('supervisor_id')
                ->references('supervisor_id')
                ->on('supervisors')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        try {
            Schema::table('tutor_work_detail_approvals', function (Blueprint $table) {
                $table->dropForeign(['supervisor_id']);
            });
        } catch (\Exception $e) {
            // ignore
        }

        DB::statement('ALTER TABLE `tutor_work_detail_approvals` MODIFY `supervisor_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('tutor_work_detail_approvals', function (Blueprint $table) {
            $table->foreign('supervisor_id')
                ->references('supervisor_id')
                ->on('supervisors')
                ->onDelete('cascade');
        });
    }
};
