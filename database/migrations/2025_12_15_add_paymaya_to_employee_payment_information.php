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
        Schema::table('employee_payment_information', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_payment_information', 'paymaya_number')) {
                $table->string('paymaya_number')->nullable()->after('paypal_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payment_information', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payment_information', 'paymaya_number')) {
                $table->dropColumn('paymaya_number');
            }
        });
    }
};
