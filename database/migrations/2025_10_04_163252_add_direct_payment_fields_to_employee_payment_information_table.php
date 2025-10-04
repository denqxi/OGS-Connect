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
            // Add direct payment method fields for simpler form handling
            $table->enum('payment_method', [
                'gcash', 
                'paymaya', 
                'bank_transfer', 
                'paypal', 
                'cash'
            ])->nullable()->after('employee_type');
            
            $table->string('bank_name')->nullable()->after('payment_method');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_name')->nullable()->after('account_number');
            $table->string('paypal_email')->nullable()->after('account_name');
            $table->string('gcash_number')->nullable()->after('paypal_email');
            $table->string('paymaya_number')->nullable()->after('gcash_number');
            
            // Make payment_method_id nullable since we're using direct fields
            $table->unsignedBigInteger('payment_method_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'bank_name',
                'account_number',
                'account_name',
                'paypal_email',
                'gcash_number',
                'paymaya_number'
            ]);
            
            // Make payment_method_id required again
            $table->unsignedBigInteger('payment_method_id')->nullable(false)->change();
        });
    }
};