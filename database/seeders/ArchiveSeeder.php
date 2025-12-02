<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'rejected_application',
            'withdrawn_application',
            'failed_screening',
            'no_show_interview',
            'incomplete_requirements',
            'duplicate_application',
        ];

        $statuses = ['archived', 'archived', 'archived'];
        
        $archiveNotes = [
            'rejected' => 'Application rejected due to not meeting minimum qualifications.',
            'declined' => 'Applicant declined the position after initial discussion.',
            'not_recommended' => 'Interview performance was below expected standards.',
            'no_answer' => 'Did not show up for scheduled interview multiple times.',
            're_schedule' => 'Multiple reschedule attempts, exceeded maximum allowed.',
        ];

        // Get applicants who should be archived based on their application status
        $archivedStatuses = ['rejected', 'declined', 'not_recommended', 'no_answer', 're_schedule'];
        $applicantsToArchive = DB::table('applicants')
            ->join('application', 'applicants.applicant_id', '=', 'application.applicant_id')
            ->whereIn('application.status', $archivedStatuses)
            ->select('applicants.applicant_id', 'application.status')
            ->get();
        
        $supervisors = DB::table('supervisor')->pluck('supervisor_id')->toArray();

        if ($applicantsToArchive->isEmpty() || empty($supervisors)) {
            $this->command->warn('No applicants to archive or no supervisors found. Please run ApplicantSeeder and SupervisorSeeder first.');
            return;
        }

        $archiveCount = 0;
        foreach ($applicantsToArchive as $index => $applicantData) {
            $supervisorId = $supervisors[$index % count($supervisors)];
            $applicationStatus = $applicantData->status;
            
            // Map application status to archive category
            $categoryMap = [
                'rejected' => 'rejected_application',
                'declined' => 'withdrawn_application',
                'not_recommended' => 'failed_screening',
                'no_answer' => 'no_show_interview',
                're_schedule' => 'incomplete_requirements',
            ];
            
            $category = $categoryMap[$applicationStatus] ?? 'rejected_application';
            $notes = $archiveNotes[$applicationStatus] ?? 'Application archived.';
            
            // Create sample payload data
            $payload = [
                'reason' => $notes,
                'archived_from' => 'application',
                'original_status' => $applicationStatus,
                'related_records' => [
                    'application_id' => $applicantData->applicant_id,
                ],
            ];

            DB::table('archive')->insert([
                'applicant_id' => $applicantData->applicant_id,
                'archive_by' => $supervisorId,
                'notes' => $notes,
                'archive_date_time' => Carbon::now()->subDays(rand(1, 60))->setTime(rand(9, 17), rand(0, 59)),
                'category' => $category,
                'status' => 'archived',
                'payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $archiveCount++;
        }

        $this->command->info("Created {$archiveCount} archive records based on application status.");
    }
}
