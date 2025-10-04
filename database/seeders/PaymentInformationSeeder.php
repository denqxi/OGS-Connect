<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeePaymentInformation;
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
        $philippinePaymentMethods = ['gcash', 'bank_transfer', 'paymaya', 'paypal'];
        
        foreach ($tutors as $index => $tutor) {
            $paymentMethodName = $philippinePaymentMethods[$index % count($philippinePaymentMethods)];
            
            $paymentData = [
                'employee_id' => $tutor->tutorID,
                'employee_type' => 'tutor',
                'payment_method' => $paymentMethodName,
                'hourly_rate' => 150.00,
                'payment_frequency' => 'monthly',
                'notes' => 'Standard tutor payment setup',
                'is_active' => true,
            ];

            // Add method-specific fields
            if ($paymentMethodName === 'bank_transfer') {
                $paymentData['account_number'] = (string)rand(1000000000, 9999999999);
                $paymentData['account_name'] = $tutor->full_name;
                $paymentData['bank_name'] = 'BDO';
            } elseif ($paymentMethodName === 'gcash') {
                $paymentData['gcash_number'] = '09' . rand(100000000, 999999999);
            } elseif ($paymentMethodName === 'paymaya') {
                $paymentData['paymaya_number'] = '09' . rand(100000000, 999999999);
            } elseif ($paymentMethodName === 'paypal') {
                $paymentData['paypal_email'] = strtolower($tutor->first_name . '.' . $tutor->last_name . '@example.com');
            }

            EmployeePaymentInformation::create($paymentData);
        }

        // Add payment information for supervisors who don't have it yet
        $supervisors = Supervisor::whereDoesntHave('paymentInformation')->get();
        $supervisorPaymentMethods = ['bank_transfer', 'gcash'];
        
        foreach ($supervisors as $index => $supervisor) {
            $paymentMethodName = $supervisorPaymentMethods[$index % count($supervisorPaymentMethods)];
            
            $paymentData = [
                'employee_id' => $supervisor->supID,
                'employee_type' => 'supervisor',
                'payment_method' => $paymentMethodName,
                'monthly_salary' => 25000.00,
                'payment_frequency' => 'monthly',
                'notes' => 'Supervisor salary setup',
                'is_active' => true,
            ];

            // Add method-specific fields
            if ($paymentMethodName === 'bank_transfer') {
                $paymentData['account_number'] = (string)rand(1000000000, 9999999999);
                $paymentData['account_name'] = $supervisor->sfname . ' ' . $supervisor->slname;
                $paymentData['bank_name'] = 'BPI';
            } elseif ($paymentMethodName === 'gcash') {
                $paymentData['gcash_number'] = '09' . rand(100000000, 999999999);
            }

            EmployeePaymentInformation::create($paymentData);
        }
    }
}