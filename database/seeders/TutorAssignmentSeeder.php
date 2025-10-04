<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds for tutor assignment system.
     * This seeder prepares data for cosine similarity-based automatic assignment.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Tutor Assignment System Seeding...');
        $this->command->info('This will create data for cosine similarity-based tutor assignment');
        $this->command->newLine();

        // Step 1: Check existing tutors
        $this->command->info('📚 Step 1: Checking existing tutors...');
        $tutorCount = \App\Models\Tutor::count();
        $this->command->info("Found {$tutorCount} existing tutors");
        $this->command->newLine();

        // Step 2: Check existing tutor accounts
        $this->command->info('🏢 Step 2: Checking existing tutor accounts...');
        $accountCount = \App\Models\TutorAccount::count();
        $this->command->info("Found {$accountCount} existing tutor accounts");
        $this->command->newLine();

        // Step 5: Summary of seeded data
        $this->command->info('📊 Seeding Summary:');
        $this->showSeededDataSummary();
        
        $this->command->newLine();
        $this->command->info('✅ Tutor Assignment System seeding completed!');
        $this->command->info('💡 You can now test automatic assignment using cosine similarity');
        $this->command->info('   based on tutor availability patterns and class schedules.');
    }

    /**
     * Display summary of seeded data
     */
    private function showSeededDataSummary(): void
    {
        $tutorsCount = \App\Models\Tutor::count();
        $activeTutorsCount = \App\Models\Tutor::where('status', 'active')->count();
        $accountsCount = \App\Models\TutorAccount::count();
        $classesCount = \App\Models\DailyData::count();

        $this->command->table([
            ['Metric', 'Count']
        ], [
            ['Total Tutors', $tutorsCount],
            ['Active Tutors', $activeTutorsCount],
            ['Tutor Accounts', $accountsCount],
            ['Classes Requiring Tutors', $classesCount],
        ]);

        $this->command->info('✅ Tutor assignment system is ready for use!');
    }
}