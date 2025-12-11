<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Applicant;
use App\Models\Tutor;
use App\Models\WorkPreference;
use App\Models\ScheduleDailyData;
use App\Models\AssignedDailyData;
use Carbon\Carbon;

class AvailableTutorTestSeeder extends Seeder
{
    /**
     * Seed a guaranteed-available GLS tutor and a matching schedule for manual testing.
     */
    public function run(): void
    {
        $targetDate = Carbon::create(2025, 12, 8);
        $targetTime = '14:00:00';

        // Ensure GLS account exists (case-insensitive lookup to match existing seeds)
        $account = Account::whereRaw('LOWER(account_name) = ?', ['gls'])->first();
        if (! $account) {
            $account = Account::create([
                'account_name' => 'GLS',
                'description' => 'GLS - Demo account for supervisor/tutor assignment tests',
                'industry' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create a fully populated applicant/tutor profile
        $applicant = Applicant::updateOrCreate(
            ['email' => 'gls.demo.tutor@ogsconnect.test'],
            [
                'first_name' => 'Demo',
                'middle_name' => 'GLS',
                'last_name' => 'Tutor',
                'birth_date' => '1992-04-18',
                'address' => '123 Demo Street, Manila',
                'contact_number' => '09998887777',
                'ms_teams' => 'gls.demo.tutor@ogsconnect.test',
                'interview_time' => now(),
                'updated_at' => now(),
            ]
        );

        $tutor = Tutor::updateOrCreate(
            ['email' => 'gls.demo.tutor@ogsconnect.test'],
            [
                'applicant_id' => $applicant->applicant_id,
                'account_id' => $account->account_id,
                'tutorID' => 'OGS-DEMO-GLS',
                'username' => 'gls.demo',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'updated_at' => now(),
            ]
        );

        // Make the tutor broadly available (covers the screenshot date/time)
        WorkPreference::updateOrCreate(
            ['applicant_id' => $applicant->applicant_id],
            [
                'platform' => ['Online', 'F2F'],
                'can_teach' => ['English', 'Literature'],
                'start_time' => '06:00:00',
                'end_time' => '23:00:00',
                'days_available' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'timezone' => 'Asia/Manila',
                'updated_at' => now(),
            ]
        );

        // Create a matching schedule entry so the tutor can be selected in the modal
        $schedule = ScheduleDailyData::firstOrCreate(
            [
                'date' => $targetDate->toDateString(),
                'time' => $targetTime,
                'school' => 'Oxford Academy',
                'class' => 'English Literature',
            ],
            [
                'day' => $targetDate->format('l'),
                'duration' => 50,
            ]
        );

        AssignedDailyData::firstOrCreate(
            ['schedule_daily_data_id' => $schedule->id],
            [
                'class_status' => 'not_assigned',
                'main_tutor' => null,
                'backup_tutor' => null,
                'assigned_supervisor' => null,
                'notes' => null,
            ]
        );

        if ($this->command) {
            $this->command->info('✅ GLS demo tutor and schedule seeded.');
            $this->command->info('   Tutor username/email: gls.demo / gls.demo.tutor@ogsconnect.test');
            $this->command->info('   Password: password123');
            $this->command->info('   Schedule: ' . $targetDate->toDateString() . ' at ' . $targetTime . ' (Oxford Academy - English Literature)');
            $this->command->info('   Use this while logged in as a GLS supervisor.');
        }

        Log::info('AvailableTutorTestSeeder seeded demo tutor and schedule', [
            'tutor_id' => $tutor->tutor_id,
            'account_id' => $account->account_id,
            'schedule_id' => $schedule->id,
        ]);
    }
}
