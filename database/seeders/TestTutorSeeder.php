<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Applicant;
use App\Models\Account;
use App\Models\Tutor;
use App\Models\WorkPreference;

class TestTutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a test tutor with complete data for testing notifications and assignments
     */
    public function run(): void
    {
        // 1. Create or get GLS account
        $account = Account::firstOrCreate(
            ['account_name' => 'GLS'],
            [
                'account_id' => DB::table('accounts')->max('account_id') + 1,
                'account_type' => 'School',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 2. Create or update test applicant
        $applicant = Applicant::updateOrCreate(
            ['email' => 'testtutor@ogsconnect.test'],
            [
                'first_name' => 'TestTutor',
                'middle_name' => 'Testing',
                'last_name' => 'Notification',
                'birth_date' => '1995-05-15',
                'address' => '123 Test Street, Test City',
                'contact_number' => '09123456789',
                'ms_teams' => 'testtutor@ogsconnect.test',
                'interview_time' => now(),
                'updated_at' => now()
            ]
        );

        // 3. Create or update test tutor
        $tutor = Tutor::updateOrCreate(
            ['username' => 'testtutor'],
            [
                'applicant_id' => $applicant->applicant_id,
                'account_id' => $account->account_id,
                'tutorID' => 'OGS-TEST001',
                'email' => 'testtutor@ogsconnect.test',
                'password' => Hash::make('password'), // Default password: password
                'status' => 'active',
                'updated_at' => now()
            ]
        );

        // 4. Create or update work preferences
        WorkPreference::updateOrCreate(
            ['applicant_id' => $applicant->applicant_id],
            [
                'platform' => ['Online', 'F2F'],
                'can_teach' => ['English', 'Math', 'Science'],
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'days_available' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'timezone' => 'Asia/Manila',
                'updated_at' => now()
            ]
        );

        $this->command->info('✅ Test tutor created successfully!');
        $this->command->info('');
        $this->command->info('📋 Tutor Details:');
        $this->command->info('   Tutor ID: OGS-TEST001');
        $this->command->info('   Name: TestTutor Testing Notification');
        $this->command->info('   Email: testtutor@ogsconnect.test');
        $this->command->info('   Username: testtutor');
        $this->command->info('   Password: password');
        $this->command->info('   Account: GLS');
        $this->command->info('   Database ID: ' . $tutor->tutor_id);
        $this->command->info('');
        $this->command->info('🔔 This tutor can now receive notifications when assigned to schedules!');
        $this->command->info('   Use tutor_id ' . $tutor->tutor_id . ' when assigning schedules for testing.');
    }
}
