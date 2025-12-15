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
        Schema::table('archives', function (Blueprint $table) {
            // Add account_id column if it doesn't exist
            if (!Schema::hasColumn('archives', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('applicant_id');
            }
            
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('archives', 'status')) {
                $table->string('status')->nullable()->after('type');
            }
            
            // Add other missing columns if needed
            if (!Schema::hasColumn('archives', 'archive_by')) {
                $table->string('archive_by')->nullable()->after('applicant_id');
            }
            
            if (!Schema::hasColumn('archives', 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }
            
            if (!Schema::hasColumn('archives', 'archive_date_time')) {
                $table->dateTime('archive_date_time')->nullable()->after('archive_date');
            }
            
            if (!Schema::hasColumn('archives', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('archives', 'payload')) {
                $table->json('payload')->nullable()->after('details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->dropColumn([
                'account_id',
                'status',
                'archive_by',
                'notes',
                'archive_date_time',
                'category',
                'payload'
            ]);
        });
    }
};
