<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SingleTutorPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $tutorId = 18;
        $subs = ['email','pdf','print'];
        $statuses = ['sent','pending','failed','draft'];

        // Create bi-monthly (semi-monthly) payroll records - 2 per month for 3 months = 6 records
        for ($i = 5; $i >= 0; $i--) {
            $isFirstHalf = ($i % 2 == 0);
            $monthsBack = intdiv($i, 2);
            $date = Carbon::now()->subMonths($monthsBack);
            
            if ($isFirstHalf) {
                $date = $date->copy()->startOfMonth(); // 1st to 15th
                $payPeriod = $date->format('Y-m') . ' (1-15)';
                $submittedDate = $date->copy()->day(15)->endOfDay();
            } else {
                $date = $date->copy()->day(16); // 16th to end of month
                $payPeriod = $date->format('Y-m') . ' (16-30)';
                $submittedDate = $date->copy()->endOfMonth();
            }

            DB::table('payroll_history')->insert([
                'tutor_id' => $tutorId,
                'pay_period' => $payPeriod,
                'total_amount' => rand(60000,140000) / 100,
                'submission_type' => $subs[array_rand($subs)],
                'status' => $statuses[array_rand($statuses)],
                'recipient_email' => 'christopher.white@example.com',
                'notes' => 'Seeded payroll for ' . $payPeriod,
                'submitted_at' => $submittedDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
