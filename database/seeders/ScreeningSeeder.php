<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScreeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phases = ['screening', 'training', 'demo'];
        $results = ['passed', 'pending', 'passed'];
        
        $screeningNotes = [
            'Excellent communication skills, demonstrated strong English proficiency.',
            'Good technical understanding, proceeding to next phase.',
            'Outstanding teaching demo, very engaging with students.',
            'Passed all requirements, recommended for onboarding.',
            'Strong candidate with relevant experience.',
            'Excellent rapport with students, natural teaching ability.',
            'Completed screening phase successfully.',
            'Very well-prepared, good presentation skills.',
        ];

        // Get all applicants with 'pending' status who would proceed to screening
        // Exclude those with rejected, no_answer, re_schedule, declined, not_recommended
        $applicantIds = DB::table('applicants')
            ->join('application', 'applicants.applicant_id', '=', 'application.applicant_id')
            ->where('application.status', 'pending')
            ->pluck('applicants.applicant_id')
            ->toArray();
        
        $supervisors = DB::table('supervisor')->pluck('supervisor_id')->toArray();
        $accounts = DB::table('accounts')->pluck('account_id')->toArray();

        if (empty($applicantIds) || empty($supervisors) || empty($accounts)) {
            $this->command->warn('No pending applicants, supervisors, or accounts found. Please run ApplicantSeeder, SupervisorSeeder, and AccountSeeder first.');
            return;
        }

        // Create screening records for pending applicants (about 70% of pending applicants)
        $screeningCount = max(1, (int) ceil(count($applicantIds) * 0.7));
        $selectedApplicants = array_slice($applicantIds, 0, $screeningCount);

        foreach ($selectedApplicants as $index => $applicantId) {
            $supervisorId = $supervisors[$index % count($supervisors)];
            $accountId = $accounts[$index % count($accounts)];
            $phase = $phases[$index % count($phases)];
            $result = $results[$index % count($results)];
            $notes = $screeningNotes[$index % count($screeningNotes)];
            
            DB::table('screening')->insert([
                'applicant_id' => $applicantId,
                'supervisor_id' => $supervisorId,
                'account_id' => $accountId,
                'phase' => $phase,
                'results' => $result,
                'notes' => $notes,
                'screening_date_time' => Carbon::now()->subDays(rand(1, 30))->setTime(rand(9, 17), rand(0, 59)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Created {$screeningCount} screening records for pending applicants.");
    }
}
