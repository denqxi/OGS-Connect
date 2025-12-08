<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tutor;
use App\Models\TutorWorkDetail;
use App\Models\PayrollHistory;
use App\Models\PayrollFinalization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizePayrollPeriod extends Command
{
    protected $signature = 'payroll:finalize {--period= : Pay period to finalize (e.g., "2025-12 (1-15)" or "current")}';
    protected $description = 'Finalize payroll for a bi-monthly period and create locked PayrollHistory records';

    public function handle()
    {
        try {
            $periodOption = $this->option('period');
            
            if (!$periodOption || $periodOption === 'current') {
                $payPeriod = $this->getCurrentPayPeriod();
                $this->info("Auto-detected current period: $payPeriod");
            } else {
                $payPeriod = $periodOption;
            }

            // Validate and parse pay period
            if (!preg_match('/^(\d{4})-(\d{2})\s+\((\d{1,2})-(\d{1,2})\)$/', $payPeriod, $matches)) {
                $this->error("Invalid pay period format. Expected: 2025-12 (1-15)");
                return 1;
            }

            [$fullMatch, $year, $month, $startDay, $endDay] = $matches;
            $periodStart = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
            $periodEnd = Carbon::createFromDate($year, $month, $endDay)->endOfDay();

            $this->info("Finalizing payroll for period: $payPeriod");
            $this->info("Period: {$periodStart->format('M d, Y')} to {$periodEnd->format('M d, Y')}");

            // Get all active tutors
            $tutors = Tutor::where('status', 'active')->get();

            if ($tutors->isEmpty()) {
                $this->warn('No active tutors found');
                return 0;
            }

            $this->info("Processing " . $tutors->count() . " tutors...");
            $bar = $this->output->createProgressBar($tutors->count());

            $createdCount = 0;
            $skippedCount = 0;

            foreach ($tutors as $tutor) {
                try {
                    // Check if PayrollHistory already exists for this period
                    $existing = PayrollHistory::where('tutor_id', $tutor->tutor_id)
                        ->where('pay_period', $payPeriod)
                        ->first();

                    if ($existing) {
                        $skippedCount++;
                        $bar->advance();
                        continue;
                    }

                    // Calculate total amount from approved work details in this period
                    $workDetails = TutorWorkDetail::where('tutor_id', $tutor->tutor_id)
                        ->where('status', 'approved')
                        ->whereBetween('created_at', [$periodStart, $periodEnd])
                        ->get();

                    $totalAmount = $workDetails->reduce(function ($carry, $wd) {
                        if (($wd->work_type ?? '') === 'hourly') {
                            $hours = ($wd->duration_minutes ?? 0) / 60;
                            $carry += ($wd->rate_per_hour ?? 0) * $hours;
                        } else {
                            $carry += ($wd->rate_per_class ?? 0);
                        }
                        return $carry;
                    }, 0);

                    $totalAmount = round($totalAmount, 2);

                    // Create PayrollHistory record with locked amount
                    PayrollHistory::create([
                        'tutor_id' => $tutor->tutor_id,
                        'pay_period' => $payPeriod,
                        'total_amount' => $totalAmount,
                        'submission_type' => 'email',
                        'status' => 'draft',
                        'recipient_email' => $tutor->email,
                        'notes' => "Auto-finalized payroll for $payPeriod. Work details count: " . $workDetails->count(),
                        'submitted_at' => now(),
                    ]);

                    // Create PayrollFinalization record to track the locked finalization
                    PayrollFinalization::create([
                        'tutor_id' => $tutor->tutor_id,
                        'pay_period' => $payPeriod,
                        'total_amount' => $totalAmount,
                        'work_details_count' => $workDetails->count(),
                        'status' => 'draft',
                        'finalized_at' => now(),
                        'notes' => "Payroll amount locked at finalization. {$workDetails->count()} approved work details.",
                    ]);

                    $createdCount++;
                } catch (\Exception $e) {
                    $this->warn("Error processing tutor {$tutor->username}: " . $e->getMessage());
                    Log::error('PayrollFinalize error', ['tutor_id' => $tutor->tutor_id, 'error' => $e->getMessage()]);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $this->info("✓ Payroll finalization complete!");
            $this->line("  Created: $createdCount records");
            $this->line("  Skipped: $skippedCount (already finalized)");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('PayrollFinalize command error', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Get the current bi-monthly pay period
     */
    private function getCurrentPayPeriod(): string
    {
        $today = Carbon::now();
        $day = $today->day;

        if ($day <= 15) {
            return $today->format('Y-m') . ' (1-15)';
        } else {
            return $today->format('Y-m') . ' (16-30)';
        }
    }
}
