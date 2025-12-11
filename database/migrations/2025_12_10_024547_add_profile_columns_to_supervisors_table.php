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
        Schema::table('supervisors', function (Blueprint $table) {
            // Add profile photo column
            $table->string('profile_photo')->nullable()->after('password');
            
            // Add payment information as JSON
            $table->json('payment_info')->nullable()->after('profile_photo');
            
            // Add additional personal info columns that might be missing
            if (!Schema::hasColumn('supervisors', 'saddress')) {
                $table->text('saddress')->nullable()->after('contact_number');
            }
            
            if (!Schema::hasColumn('supervisors', 'sshift')) {
                $table->string('sshift')->nullable()->after('timezone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropColumn(['profile_photo', 'payment_info']);
            
            if (Schema::hasColumn('supervisors', 'saddress')) {
                $table->dropColumn('saddress');
            }
            
            if (Schema::hasColumn('supervisors', 'sshift')) {
                $table->dropColumn('sshift');
            }
        });
    }
};
