<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeePaymentInformation;
use App\Models\EmployeePaymentDetails;
use App\Models\PaymentMethodDetails;
use App\Models\Tutor;
use App\Models\Supervisor;

class PaymentInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add payment information for tutors who don't have it yet
        $tutors = Tutor::whereDoesntHave('paymentInformation')->get();
        $philippinePaymentMethods = ['gcash', 'bdo', 'bpi', 'paymaya', 'metrobank'];
        
        foreach ($tutors as $index => $tutor) {
            $paymentMethodName = $philippinePaymentMethods[$index % count($philippinePaymentMethods)];
            
            // Get the payment method details
            $paymentMethod = PaymentMethodDetails::where('payment_method_name', $paymentMethodName)->first();
            
            $paymentData = [
                'employee_id' => $tutor->tutorID,
                'employee_type' => 'tutor',
                'payment_method_id' => $paymentMethod->id,
                'hourly_rate' => 150.00,
                'payment_frequency' => 'monthly',
                'notes' => 'Standard tutor payment setup',
                'is_active' => true,
            ];

            $paymentInfo = EmployeePaymentInformation::create($paymentData);

            // Add method-specific fields to the normalized table
            if (in_array($paymentMethodName, ['bdo', 'bpi', 'metrobank'])) {
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'account_number',
                    'field_value' => (string)rand(1000000000, 9999999999)
                ]);
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'account_name',
                    'field_value' => $tutor->full_name
                ]);
            } elseif ($paymentMethodName === 'gcash') {
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'gcash_number',
                    'field_value' => '09' . rand(100000000, 999999999)
                ]);
            } elseif ($paymentMethodName === 'paymaya') {
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'paymaya_number',
                    'field_value' => '09' . rand(100000000, 999999999)
                ]);
            }
        }

        // Add payment information for supervisors who don't have it yet
        $supervisors = Supervisor::whereDoesntHave('paymentInformation')->get();
        $supervisorPaymentMethods = ['bdo', 'bpi', 'unionbank'];
        
        foreach ($supervisors as $index => $supervisor) {
            $paymentMethodName = $supervisorPaymentMethods[$index % count($supervisorPaymentMethods)];
            
            // Get the payment method details
            $paymentMethod = PaymentMethodDetails::where('payment_method_name', $paymentMethodName)->first();
            
            $paymentData = [
                'employee_id' => $supervisor->supID,
                'employee_type' => 'supervisor',
                'payment_method_id' => $paymentMethod->id,
                'monthly_salary' => 25000.00,
                'payment_frequency' => 'monthly',
                'notes' => 'Supervisor salary setup',
                'is_active' => true,
            ];

            $paymentInfo = EmployeePaymentInformation::create($paymentData);

            // Add method-specific fields to the normalized table
            if (in_array($paymentMethodName, ['bdo', 'bpi', 'unionbank'])) {
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'account_number',
                    'field_value' => (string)rand(1000000000, 9999999999)
                ]);
                EmployeePaymentDetails::create([
                    'employee_payment_id' => $paymentInfo->id,
                    'field_name' => 'account_name',
                    'field_value' => $supervisor->full_name
                ]);
            }
        }

        // Add some variety with different payment methods
        $tutor = Tutor::skip(5)->first();
        if ($tutor) {
            $gcashMethod = PaymentMethodDetails::where('payment_method_name', 'gcash')->first();
            $paymentInfo = EmployeePaymentInformation::create([
                'employee_id' => $tutor->tutorID,
                'employee_type' => 'tutor',
                'payment_method_id' => $gcashMethod->id,
                'hourly_rate' => 200.00,
                'payment_frequency' => 'weekly',
                'notes' => 'GCash payment for remote tutor',
                'is_active' => true,
            ]);
            
            EmployeePaymentDetails::create([
                'employee_payment_id' => $paymentInfo->id,
                'field_name' => 'gcash_number',
                'field_value' => '09171234567'
            ]);
        }

        $supervisor = Supervisor::skip(3)->first();
        if ($supervisor) {
            $landbankMethod = PaymentMethodDetails::where('payment_method_name', 'landbank')->first();
            $paymentInfo = EmployeePaymentInformation::create([
                'employee_id' => $supervisor->supID,
                'employee_type' => 'supervisor',
                'payment_method_id' => $landbankMethod->id,
                'monthly_salary' => 30000.00,
                'payment_frequency' => 'monthly',
                'notes' => 'Landbank payment for government supervisor',
                'is_active' => true,
            ]);
            
            EmployeePaymentDetails::create([
                'employee_payment_id' => $paymentInfo->id,
                'field_name' => 'account_number',
                'field_value' => (string)rand(1000000000, 9999999999)
            ]);
            EmployeePaymentDetails::create([
                'employee_payment_id' => $paymentInfo->id,
                'field_name' => 'account_name',
                'field_value' => $supervisor->full_name
            ]);
        }
    }
}