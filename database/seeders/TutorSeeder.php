<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\Applicant;
use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if tutors already exist
        if (Tutor::count() > 0) {
            if ($this->command) {
                $this->command->info('Tutors already exist, skipping...');
            }
            return;
        }

        // First, ensure we have an account (GLS) for the tutor
        $account = Account::firstOrCreate(
            ['account_name' => 'GLS'],
            [
                'description' => 'Global Learning Solutions',
                'industry' => 'Education',
            ]
        );

        // Create a test applicant first (required for tutor)
        $applicant = Applicant::create([
            'first_name' => 'Test',
            'middle_name' => 'Tutor',
            'last_name' => 'User',
            'birth_date' => '1995-01-15',
            'address' => '123 Test Street, Test City',
            'contact_number' => '09171234567',
            'email' => 'test.tutor@example.com',
            'ms_teams' => 'test.tutor@example.com',
            'interview_time' => now(),
        ]);

        // Create test tutor with new normalized structure
        $tutor = Tutor::create([
            'applicant_id' => $applicant->applicant_id,
            'account_id' => $account->account_id,
            'tutorID' => 'OGS-T0001',
            'username' => 'testtutor',
            'email' => 'test.tutor@example.com',
            'password' => 'tutor1234', // Will be hashed automatically by the model
            'status' => 'active',
        ]);

        if ($this->command) {
            $this->command->info('✅ Created test tutor account:');
            $this->command->info('   - Email/ID: test.tutor@example.com or OGS-T0001');
            $this->command->info('   - Username: testtutor');
            $this->command->info('   - Password: tutor1234');
            $this->command->info('   - Name: Test Tutor User');
            $this->command->info('   - Account: GLS');
        }
    }
}
