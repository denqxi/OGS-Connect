<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethodDetails;

class PaymentMethodDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if payment methods already exist
        if (PaymentMethodDetails::count() > 0) {
            if ($this->command) {
                $this->command->info('Payment methods already exist, skipping...');
            }
            return;
        }

        $paymentMethods = [
            [
                'payment_method_name' => 'gcash',
                'required_fields' => ['gcash_number'],
                'field_validation_rules' => ['gcash_number' => 'required|string|min:11|max:11'],
                'description' => 'GCash mobile wallet',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'paymaya',
                'required_fields' => ['paymaya_number'],
                'field_validation_rules' => ['paymaya_number' => 'required|string|min:11|max:11'],
                'description' => 'PayMaya mobile wallet',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'bdo',
                'required_fields' => ['account_number', 'account_name'],
                'field_validation_rules' => [
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'BDO Bank Transfer',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'bpi',
                'required_fields' => ['account_number', 'account_name'],
                'field_validation_rules' => [
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'BPI Bank Transfer',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'metrobank',
                'required_fields' => ['account_number', 'account_name'],
                'field_validation_rules' => [
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'Metrobank Bank Transfer',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'landbank',
                'required_fields' => ['account_number', 'account_name'],
                'field_validation_rules' => [
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'Landbank Bank Transfer',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'unionbank',
                'required_fields' => ['account_number', 'account_name'],
                'field_validation_rules' => [
                    'account_number' => 'required|string|min:10|max:12',
                    'account_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'UnionBank Bank Transfer',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'paypal',
                'required_fields' => ['paypal_email'],
                'field_validation_rules' => ['paypal_email' => 'required|email'],
                'description' => 'PayPal International',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'remittance',
                'required_fields' => ['remittance_center', 'recipient_name'],
                'field_validation_rules' => [
                    'remittance_center' => 'required|string|min:2|max:50',
                    'recipient_name' => 'required|string|min:2|max:100'
                ],
                'description' => 'Money Transfer/Remittance',
                'is_active' => true
            ],
            [
                'payment_method_name' => 'cash',
                'required_fields' => [],
                'field_validation_rules' => [],
                'description' => 'Cash Payment',
                'is_active' => true
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethodDetails::create($method);
        }

        $this->command->info('✅ Created ' . count($paymentMethods) . ' payment method details');
    }
}
