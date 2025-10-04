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
        // Remove the foreign key constraint first
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
        
        // Drop the payment_method_details table
        Schema::dropIfExists('payment_method_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the payment_method_details table
        Schema::create('payment_method_details', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method_name');
            $table->json('required_fields');
            $table->json('field_validation_rules')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('payment_method_name');
        });
        
        // Add back the payment_method_id column and foreign key
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('employee_type');
            $table->foreign('payment_method_id')->references('id')->on('payment_method_details')->onDelete('restrict');
        });
        
        // Re-seed the payment method details
        $this->seedPaymentMethodDetails();
    }
    
    /**
     * Seed payment method details for rollback
     */
    private function seedPaymentMethodDetails()
    {
        $paymentMethods = [
            [
                'payment_method_name' => 'gcash',
                'required_fields' => json_encode(['gcash_number']),
                'field_validation_rules' => json_encode(['gcash_number' => 'required|string|min:11|max:11']),
                'description' => 'GCash mobile wallet',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'paymaya',
                'required_fields' => json_encode(['paymaya_number']),
                'field_validation_rules' => json_encode(['paymaya_number' => 'required|string|min:11|max:11']),
                'description' => 'PayMaya mobile wallet',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'paypal',
                'required_fields' => json_encode(['paypal_email']),
                'field_validation_rules' => json_encode(['paypal_email' => 'required|email']),
                'description' => 'PayPal online payment',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'bank_transfer',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ]),
                'description' => 'Bank Transfer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'cash',
                'required_fields' => json_encode([]),
                'field_validation_rules' => json_encode([]),
                'description' => 'Cash payment',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('payment_method_details')->insert($paymentMethods);
    }
};