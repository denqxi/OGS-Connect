<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            if (Schema::hasColumn('supervisors', 'accID')) {
                $table->dropColumn('accID');
            }
            if (!Schema::hasColumn('supervisors', 'password')) {
                $table->string('password')->after('sconNum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->string('accID', 50)->nullable();
            $table->dropColumn('password');
        });
    }
};
