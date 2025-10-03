<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutor;
use App\Models\TutorDetails;
use Carbon\Carbon;

class TutorDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👤 Creating tutor details...');
        
        $tutors = Tutor::all();
        $createdCount = 0;

        // Sample addresses
        $addresses = [
            'Quezon City, Metro Manila',
            'Makati City, Metro Manila',
            'Cebu City, Cebu',
            'Davao City, Davao del Sur',
            'Baguio City, Benguet',
            'Iloilo City, Iloilo',
            'Cagayan de Oro City, Misamis Oriental',
            'Bacolod City, Negros Occidental',
            'Zamboanga City, Zamboanga del Sur',
            'Antipolo City, Rizal',
            'Taguig City, Metro Manila',
            'Pasig City, Metro Manila',
            'Marikina City, Metro Manila',
            'Las Piñas City, Metro Manila',
            'Muntinlupa City, Metro Manila',
            'Parañaque City, Metro Manila',
            'Valenzuela City, Metro Manila',
            'Caloocan City, Metro Manila',
            'Malabon City, Metro Manila',
            'Navotas City, Metro Manila'
        ];

        // Sample ESL experience levels
        $eslExperiences = [
            '1 year',
            '2 years',
            '3 years',
            '4 years',
            '5 years',
            '6 years',
            '7 years',
            '8 years',
            '9 years',
            '10+ years'
        ];

        // Work setup options
        $workSetups = ['WFH', 'WAS', 'Hybrid'];

        // Educational attainment options
        $educationalAttainments = [
            'High School',
            'Associate Degree',
            'Bachelors Degree',
            'Masters Degree',
            'Doctorate',
            'Other'
        ];

        foreach ($tutors as $tutor) {
            // Create a seed based on tutor ID for consistent assignment
            $seed = crc32($tutor->tutorID . 'details');
            mt_srand($seed);

            // Generate random details
            $address = $addresses[mt_rand(0, count($addresses) - 1)];
            $eslExperience = $eslExperiences[mt_rand(0, count($eslExperiences) - 1)];
            $workSetup = $workSetups[mt_rand(0, count($workSetups) - 1)];
            $educationalAttainment = $educationalAttainments[mt_rand(0, count($educationalAttainments) - 1)];

            // Generate first day of teaching (random date within last 5 years)
            $firstDayTeaching = Carbon::now()->subYears(mt_rand(0, 5))->subDays(mt_rand(0, 365));

            // Generate additional notes
            $additionalNotes = $this->generateAdditionalNotes($tutor, $eslExperience, $workSetup);

            TutorDetails::create([
                'tutor_id' => $tutor->tutorID,
                'address' => $address,
                'esl_experience' => $eslExperience,
                'work_setup' => $workSetup,
                'first_day_teaching' => $firstDayTeaching,
                'educational_attainment' => $educationalAttainment,
                'additional_notes' => $additionalNotes
            ]);

            $createdCount++;
        }

        $this->command->info("✅ Created {$createdCount} tutor details records");
    }

    /**
     * Generate additional notes based on tutor information
     */
    private function generateAdditionalNotes($tutor, $eslExperience, $workSetup): string
    {
        $notes = [];

        // Add experience-based note
        if (strpos($eslExperience, '10+') !== false) {
            $notes[] = "Highly experienced ESL instructor with extensive teaching background.";
        } elseif (strpos($eslExperience, '5') !== false || strpos($eslExperience, '6') !== false || 
                  strpos($eslExperience, '7') !== false || strpos($eslExperience, '8') !== false || 
                  strpos($eslExperience, '9') !== false) {
            $notes[] = "Experienced ESL instructor with solid teaching foundation.";
        } else {
            $notes[] = "Developing ESL instructor with growing teaching experience.";
        }

        // Add work setup note
        if ($workSetup === 'WFH') {
            $notes[] = "Comfortable with remote teaching and online platforms.";
        } elseif ($workSetup === 'WAS') {
            $notes[] = "Prefers on-site teaching environment.";
        } else {
            $notes[] = "Flexible with both remote and on-site teaching arrangements.";
        }

        // Add personality note based on name
        $firstName = strtolower($tutor->first_name);
        if (in_array($firstName, ['alice', 'bob', 'carol', 'david', 'emily'])) {
            $notes[] = "Professional and reliable instructor with good communication skills.";
        } else {
            $notes[] = "Dedicated instructor committed to student success.";
        }

        return implode(' ', $notes);
    }
}