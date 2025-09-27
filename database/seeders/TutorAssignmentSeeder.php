<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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

        // Step 1: Seed additional tutors
        $this->command->info('📚 Step 1: Adding more tutors...');
        $this->call(TutorSeeder::class);
        $this->command->newLine();

        // Step 2: Create time slots for availability matching
        $this->command->info('⏰ Step 2: Creating time slots...');
        $this->call(TimeSlotSeeder::class);
        $this->command->newLine();

        // Step 3: Create tutor availability patterns
        $this->command->info('👥 Step 3: Assigning tutor availability patterns...');
        $this->call(TutorAvailabilitySeeder::class);
        $this->command->newLine();

        // Step 4: Summary of seeded data
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
        $timeSlotsCount = \App\Models\TimeSlot::count();
        $availabilitiesCount = \App\Models\Availability::count();
        $availableCount = \App\Models\Availability::where('availStatus', 'available')->count();
        $classesCount = \App\Models\DailyData::count();

        $this->command->table([
            ['Metric', 'Count']
        ], [
            ['Total Tutors', $tutorsCount],
            ['Active Tutors', $activeTutorsCount],
            ['Time Slots Created', $timeSlotsCount],
            ['Availability Records', $availabilitiesCount],
            ['Available Slots', $availableCount],
            ['Classes Requiring Tutors', $classesCount],
        ]);

        // Show tutor availability distribution
        $this->command->info('🔍 Tutor Availability Distribution:');
        $tutors = \App\Models\Tutor::with(['availabilities' => function($query) {
            $query->where('availStatus', 'available');
        }])->get();

        foreach ($tutors as $tutor) {
            $availableSlots = $tutor->availabilities->count();
            $status = $tutor->status === 'active' ? '🟢' : '🔴';
            $this->command->info("  {$status} {$tutor->tusername}: {$availableSlots} available slots");
        }
    }
}