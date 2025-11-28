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
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->boolean('was_promoted_from_backup')->default(false)->after('is_backup');
            $table->string('replaced_tutor_name')->nullable()->after('was_promoted_from_backup');
            $table->timestamp('promoted_at')->nullable()->after('replaced_tutor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_assignments', function (Blueprint $table) {
            $table->dropColumn(['was_promoted_from_backup', 'replaced_tutor_name', 'promoted_at']);
        });
    }
};
