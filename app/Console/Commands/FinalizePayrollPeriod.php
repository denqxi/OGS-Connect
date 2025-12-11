<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tutor;
use App\Models\TutorWorkDetail;
use App\Models\PayrollHistory;
use App\Models\PayrollFinalization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\PayPeriodHelper;

class FinalizePayrollPeriod extends Command
{
    protected $signature = 'payroll:finalize {--period= : Pay period to finalize (e.g., "2025-02 (28-12)" or "current")}';
    protected $description = 'Finalize payroll for a bi-monthly period (28→12 or 13→27) and create locked PayrollHistory records';

    public function handle()
    {
        try {
            $periodOption = $this->option('period');
            
            if (!$periodOption || $periodOption === 'current') {
                $period = PayPeriodHelper::getCurrentPeriod();
                $payPeriod = $period['label'];
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
                $this->info("Auto-detected current period: $payPeriod");
            } else {
                $payPeriod = $periodOption;
                
                // Validate and parse pay period (28-12 or 13-27)
                if (!preg_match('/^(\d{4})-(\d{2})\s+\((\d{1,2})-(\d{1,2})\)$/', $payPeriod, $matches)) {
                    $this->error("Invalid pay period format. Expected: 2025-02 (28-12) or 2025-03 (13-27)");
                    return 1;
                }

                [$fullMatch, $year, $month, $startDay, $endDay] = $matches;
                
                // Determine correct boundaries based on period type
                if ($startDay == 28) {
                    // First period: 28 of prev month → 12 of this month
                    $periodStart = Carbon::createFromDate($year, $month, 1)->subMonth()->day(28)->startOfDay();
                    $periodEnd = Carbon::createFromDate($year, $month, 12)->endOfDay();
                } else {
                    // Second period: 13 → 27 of same month
                    $periodStart = Carbon::createFromDate($year, $month, 13)->startOfDay();
                    $periodEnd = Carbon::createFromDate($year, $month, 27)->endOfDay();
                }
            }

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
                    // Use transaction with lock to prevent double-finalization
                    $result = DB::transaction(function () use ($tutor, $payPeriod, $periodStart, $periodEnd) {
                        // Check if PayrollHistory already exists for this period (with lock)
                        $existing = PayrollHistory::where('tutor_id', $tutor->tutor_id)
                            ->where('pay_period', $payPeriod)
                            ->lockForUpdate()
                            ->first();

                        if ($existing) {
                            return ['created' => false];
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

                        return ['created' => true];
                    });

                    if ($result['created']) {
                        $createdCount++;
                    } else {
                        $skippedCount++;
                    }
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

}
