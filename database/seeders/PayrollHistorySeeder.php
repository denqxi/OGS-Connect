<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollHistory;
use App\Models\Tutor;
use Carbon\Carbon;

class PayrollHistorySeeder extends Seeder
{
    public function run(): void
    {
        $tutors = Tutor::where('status', 'active')->limit(5)->get();

        if ($tutors->isEmpty()) {
            echo 'No active tutors found.' . PHP_EOL;
            return;
        }

        $submissionTypes = ['email', 'pdf', 'print'];
        $statuses = ['sent', 'pending', 'failed', 'draft'];

        foreach ($tutors as $tutor) {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i)->startOfMonth();
                $payPeriod = $date->format('Y-m');

                PayrollHistory::create([
                    'tutor_id' => $tutor->tutor_id,
                    'pay_period' => $payPeriod,
                    'total_amount' => rand(50000, 150000) / 100,
                    'submission_type' => $submissionTypes[array_rand($submissionTypes)],
                    'status' => $statuses[array_rand($statuses)],
                    'recipient_email' => $tutor->email,
                    'notes' => 'Payroll for ' . $payPeriod,
                    'submitted_at' => $date->endOfMonth(),
                ]);
            }
        }
        
        echo 'PayrollHistory seeded successfully!' . PHP_EOL;
    }
}