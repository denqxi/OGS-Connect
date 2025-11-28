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
        Schema::create('payment_method_details', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method_name')->unique();
            $table->json('required_fields')->nullable(); // Fields required for this payment method
            $table->json('field_validation_rules')->nullable(); // Validation rules for fields
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert payment method details
        DB::table('payment_method_details')->insert([
            [
                'payment_method_name' => 'gcash',
                'required_fields' => json_encode(['gcash_number']),
                'field_validation_rules' => json_encode(['gcash_number' => 'required|string|regex:/^09[0-9]{9}$/']),
                'description' => 'GCash mobile payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'paymaya',
                'required_fields' => json_encode(['paymaya_number']),
                'field_validation_rules' => json_encode(['paymaya_number' => 'required|string|regex:/^09[0-9]{9}$/']),
                'description' => 'PayMaya mobile payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'bdo',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|max:255'
                ]),
                'description' => 'BDO Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'bpi',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|max:255'
                ]),
                'description' => 'BPI Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'metrobank',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|max:255'
                ]),
                'description' => 'Metrobank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'landbank',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|max:255'
                ]),
                'description' => 'Landbank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'unionbank',
                'required_fields' => json_encode(['account_number', 'account_name']),
                'field_validation_rules' => json_encode([
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|max:255'
                ]),
                'description' => 'UnionBank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'paypal',
                'required_fields' => json_encode(['paypal_email']),
                'field_validation_rules' => json_encode(['paypal_email' => 'required|email']),
                'description' => 'PayPal payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'remittance',
                'required_fields' => json_encode(['account_name']),
                'field_validation_rules' => json_encode(['account_name' => 'required|string|max:255']),
                'description' => 'Remittance Center',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_method_name' => 'cash',
                'required_fields' => json_encode([]),
                'field_validation_rules' => json_encode([]),
                'description' => 'Cash payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_details');
    }
};
