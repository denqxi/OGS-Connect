<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, delete all existing security questions (they have wrong user_id values)
        DB::table('security_questions')->truncate();
        
        // Change user_id from string to unsignedBigInteger
        Schema::table('security_questions', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'user_id']); // Drop old index
            $table->unsignedBigInteger('user_id')->change();
            
            // Add composite index for faster lookups
            $table->index(['user_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_questions', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'user_id']);
            $table->string('user_id')->change();
            $table->index(['user_type', 'user_id']);
        });
    }
};
