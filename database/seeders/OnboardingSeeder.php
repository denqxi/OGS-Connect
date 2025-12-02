<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OnboardingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phases = [
            'onboarding',
            'Platform Training',
            'Teaching Method Training',
            'First Class Observation',
            'Mentorship Program',
        ];
        
        $onboardingNotes = [
            'Completed orientation successfully, ready for platform training.',
            'Familiar with Zoom and ClassIn, demonstrated proficiency.',
            'Excellent grasp of teaching methodologies, passed training assessment.',
            'First class went smoothly, received positive feedback from students.',
            'Progressing well under mentorship, shows great potential.',
            'Outstanding performance during onboarding, highly recommended.',
        ];

        // Get applicants who have passed screening (results = 'passed')
        $passedScreeningApplicants = DB::table('screening')
            ->where('results', 'passed')
            ->pluck('applicant_id')
            ->unique()
            ->toArray();
        
        $supervisors = DB::table('supervisor')->pluck('supervisor_id')->toArray();
        $accounts = DB::table('accounts')->pluck('account_id')->toArray();

        if (empty($passedScreeningApplicants) || empty($supervisors) || empty($accounts)) {
            $this->command->warn('No applicants with passed screening, supervisors, or accounts found. Please run ScreeningSeeder first.');
            return;
        }

        // Create onboarding records for applicants who passed screening (about 60% of those who passed)
        $onboardingCount = max(1, (int) ceil(count($passedScreeningApplicants) * 0.6));
        $selectedApplicants = array_slice($passedScreeningApplicants, 0, $onboardingCount);

        foreach ($selectedApplicants as $index => $applicantId) {
            // Get the account_id from the screening record for consistency
            $screeningRecord = DB::table('screening')
                ->where('applicant_id', $applicantId)
                ->first();
            
            $supervisorId = $supervisors[$index % count($supervisors)];
            $accountId = $screeningRecord->account_id ?? $accounts[$index % count($accounts)];
            $phase = $phases[$index % count($phases)];
            $notes = $onboardingNotes[$index % count($onboardingNotes)];
            
            DB::table('onboardings')->insert([
                'applicant_id' => $applicantId,
                'account_id' => $accountId,
                'assessed_by' => $supervisorId,
                'phase' => $phase,
                'notes' => $notes,
                'onboarding_date_time' => Carbon::now()->subDays(rand(1, 15))->setTime(rand(9, 17), rand(0, 59)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Created {$onboardingCount} onboarding records for applicants who passed screening.");
    }
}
