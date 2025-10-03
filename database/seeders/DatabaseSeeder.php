<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed core data in proper order
        $this->call([
            SupervisorSeeder::class,
            TutorSeeder::class,
            TutorDetailsSeeder::class,
            TutorAccountSeeder::class,
            PaymentInformationSeeder::class,
            TutorAssignmentSeeder::class,
            SampleScheduleSeeder::class,
        ]);
    }
}
