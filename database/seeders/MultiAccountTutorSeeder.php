<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\Applicant;
use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MultiAccountTutorSeeder extends Seeder
{
    /**
     * Run the database seeds to assign tutors to different accounts
     */
    public function run(): void
    {
        // Define tutor data for different accounts with fixed account IDs
        $tutorsData = [
            // GLS Account (ID: 1) Tutors
            [
                'account_id' => 1,
                'account_name' => 'GLS',
                'tutors' => [
                    [
                        'first_name' => 'John',
                        'middle_name' => 'Paul',
                        'last_name' => 'Smith',
                        'email' => 'john.smith@example.com',
                        'contact_number' => '09171111111',
                        'username' => 'johnsmith',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'Maria',
                        'middle_name' => 'Grace',
                        'last_name' => 'Garcia',
                        'email' => 'maria.garcia@example.com',
                        'contact_number' => '09172222222',
                        'username' => 'mariagarcia',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'Sarah',
                        'middle_name' => 'Ann',
                        'last_name' => 'Johnson',
                        'email' => 'sarah.johnson@example.com',
                        'contact_number' => '09173333333',
                        'username' => 'sarahjohnson',
                        'password' => 'tutor1234',
                    ],
                ]
            ],
            // Tutlo Account (ID: 2) Tutors
            [
                'account_id' => 2,
                'account_name' => 'Tutlo',
                'tutors' => [
                    [
                        'first_name' => 'Jennifer',
                        'middle_name' => 'Lynn',
                        'last_name' => 'Anderson',
                        'email' => 'jennifer.anderson@example.com',
                        'contact_number' => '09179999999',
                        'username' => 'jenniferanderson',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'Christopher',
                        'middle_name' => 'Lee',
                        'last_name' => 'White',
                        'email' => 'christopher.white@example.com',
                        'contact_number' => '09170000000',
                        'username' => 'christopherwhite',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'Lisa',
                        'middle_name' => 'Victoria',
                        'last_name' => 'Harris',
                        'email' => 'lisa.harris@example.com',
                        'contact_number' => '09171010101',
                        'username' => 'lisaharris',
                        'password' => 'tutor1234',
                    ],
                ]
            ],
            // Babilala Account (ID: 3) Tutors
            [
                'account_id' => 3,
                'account_name' => 'Babilala',
                'tutors' => [
                    [
                        'first_name' => 'Angela',
                        'middle_name' => 'Marie',
                        'last_name' => 'Martinez',
                        'email' => 'angela.martinez@example.com',
                        'contact_number' => '09177777777',
                        'username' => 'angelamartinez',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'James',
                        'middle_name' => 'Edward',
                        'last_name' => 'Taylor',
                        'email' => 'james.taylor@example.com',
                        'contact_number' => '09178888888',
                        'username' => 'jamestaylor',
                        'password' => 'tutor1234',
                    ],
                ]
            ],
            // Talk915 Account (ID: 4) Tutors
            [
                'account_id' => 4,
                'account_name' => 'Talk915',
                'tutors' => [
                    [
                        'first_name' => 'Michael',
                        'middle_name' => 'James',
                        'last_name' => 'Brown',
                        'email' => 'michael.brown@example.com',
                        'contact_number' => '09174444444',
                        'username' => 'michaelbrown',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'Emily',
                        'middle_name' => 'Rose',
                        'last_name' => 'Davis',
                        'email' => 'emily.davis@example.com',
                        'contact_number' => '09175555555',
                        'username' => 'emilydavis',
                        'password' => 'tutor1234',
                    ],
                    [
                        'first_name' => 'David',
                        'middle_name' => 'Robert',
                        'last_name' => 'Wilson',
                        'email' => 'david.wilson@example.com',
                        'contact_number' => '09176666666',
                        'username' => 'davidwilson',
                        'password' => 'tutor1234',
                    ],
                ]
            ],
        ];

        foreach ($tutorsData as $accountData) {
            $account_id = $accountData['account_id'];
            $account_name = $accountData['account_name'];

            foreach ($accountData['tutors'] as $tutorData) {
                // Check if tutor email already exists
                if (Applicant::where('email', $tutorData['email'])->exists()) {
                    if ($this->command) {
                        $this->command->info("Tutor {$tutorData['first_name']} {$tutorData['last_name']} already exists, skipping...");
                    }
                    continue;
                }

                // Create applicant
                $applicant = Applicant::create([
                    'first_name' => $tutorData['first_name'],
                    'middle_name' => $tutorData['middle_name'],
                    'last_name' => $tutorData['last_name'],
                    'birth_date' => '1990-01-15',
                    'address' => '123 Example Street, Test City',
                    'contact_number' => $tutorData['contact_number'],
                    'email' => $tutorData['email'],
                    'ms_teams' => $tutorData['email'],
                    'interview_time' => now(),
                ]);

                // Create tutor
                $tutor = Tutor::create([
                    'applicant_id' => $applicant->applicant_id,
                    'account_id' => $account_id,
                    'tutorID' => $this->generateTutorID(),
                    'username' => $tutorData['username'],
                    'email' => $tutorData['email'],
                    'password' => $tutorData['password'],
                    'status' => 'active',
                ]);

                if ($this->command) {
                    $this->command->info("✅ Created tutor: {$tutorData['first_name']} {$tutorData['last_name']} ({$tutor->tutorID}) for {$account_name}");
                }
            }
        }

        if ($this->command) {
            $this->command->info("\n✅ All tutors have been assigned to their respective accounts!");
            $this->command->info("Total tutors created: " . Tutor::count());
        }
    }

    /**
     * Generate a unique formatted tutor ID
     */
    private function generateTutorID(): string
    {
        $lastTutor = Tutor::whereNotNull('tutorID')
            ->where('tutorID', 'LIKE', 'OGS-T%')
            ->orderByRaw('CAST(SUBSTRING(tutorID, 6) AS UNSIGNED) DESC')
            ->first();

        if ($lastTutor && preg_match('/OGS-T(\d+)/', $lastTutor->tutorID, $matches)) {
            $nextId = ((int) $matches[1]) + 1;
        } else {
            $nextId = 1;
        }

        return 'OGS-T' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}
