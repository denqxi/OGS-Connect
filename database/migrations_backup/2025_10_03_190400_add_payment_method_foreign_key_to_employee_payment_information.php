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
        // First, add the payment_method_id column
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('employee_type');
        });
        
        // Populate the payment_method_id based on existing payment_method values
        $paymentMethods = DB::table('payment_method_details')->get()->keyBy('payment_method_name');
        
        $paymentRecords = DB::table('employee_payment_information')->get();
        
        foreach ($paymentRecords as $record) {
            if (isset($paymentMethods[$record->payment_method])) {
                DB::table('employee_payment_information')
                    ->where('id', $record->id)
                    ->update(['payment_method_id' => $paymentMethods[$record->payment_method]->id]);
            }
        }
        
        // Make the column not nullable and add foreign key
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable(false)->change();
            $table->foreign('payment_method_id')->references('id')->on('payment_method_details')->onDelete('restrict');
        });
        
        // Remove the old payment_method enum column
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the payment_method enum column
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->enum('payment_method', [
                'gcash', 
                'paymaya', 
                'bdo', 
                'bpi', 
                'metrobank', 
                'landbank', 
                'unionbank', 
                'paypal', 
                'remittance', 
                'cash'
            ])->after('employee_type');
        });
        
        // Populate the payment_method based on payment_method_id
        $paymentMethods = DB::table('payment_method_details')->get()->keyBy('id');
        
        $paymentRecords = DB::table('employee_payment_information')->get();
        
        foreach ($paymentRecords as $record) {
            if (isset($paymentMethods[$record->payment_method_id])) {
                DB::table('employee_payment_information')
                    ->where('id', $record->id)
                    ->update(['payment_method' => $paymentMethods[$record->payment_method_id]->payment_method_name]);
            }
        }
        
        // Drop the foreign key and column
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
    }
};
