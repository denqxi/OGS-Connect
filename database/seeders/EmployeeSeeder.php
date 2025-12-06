<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Applicant;
use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\Supervisor;
use App\Models\Account;
use App\Models\Qualification;
use App\Models\Requirement;
use App\Models\WorkPreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Employee Seeder...');
        
        // Get or create accounts
        $accounts = $this->getOrCreateAccounts();
        
        // Seed tutors for each account (10+ per account)
        $this->seedTutors($accounts);
        
        // Seed supervisors (10+ total)
        $this->seedSupervisors();
        
        $this->command->info('Employee Seeder completed successfully!');
    }

    /**
     * Get or create accounts
     */
    private function getOrCreateAccounts()
    {
        $accountData = [
            ['account_name' => 'GLS', 'description' => 'Global Learning Services', 'industry' => 'Education'],
            ['account_name' => 'Tutlo', 'description' => 'Tutlo Learning Platform', 'industry' => 'EdTech'],
            ['account_name' => 'Babilala', 'description' => 'Babilala Language School', 'industry' => 'Education'],
            ['account_name' => 'Talk915', 'description' => 'Talk915 Communication Platform', 'industry' => 'Communication'],
        ];

        $accounts = [];
        foreach ($accountData as $data) {
            $account = Account::firstOrCreate(
                ['account_name' => $data['account_name']],
                $data
            );
            $accounts[$data['account_name']] = $account;
        }

        return $accounts;
    }

    /**
     * Seed tutors for each account
     */
    private function seedTutors($accounts)
    {
        $this->command->info('Seeding tutors...');
        
        $tutorData = [
            // GLS Tutors (12 tutors)
            ['first_name' => 'John', 'last_name' => 'Martinez', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Robert', 'last_name' => 'Garcia', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Jennifer', 'last_name' => 'Cruz', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Michael', 'last_name' => 'Reyes', 'account' => 'GLS', 'status' => 'inactive'],
            ['first_name' => 'Lisa', 'last_name' => 'Fernandez', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'David', 'last_name' => 'Lopez', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Patricia', 'last_name' => 'Gonzales', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'James', 'last_name' => 'Rivera', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Elizabeth', 'last_name' => 'Torres', 'account' => 'GLS', 'status' => 'inactive'],
            ['first_name' => 'Richard', 'last_name' => 'Ramirez', 'account' => 'GLS', 'status' => 'active'],
            ['first_name' => 'Susan', 'last_name' => 'Flores', 'account' => 'GLS', 'status' => 'active'],
            
            // Tutlo Tutors (12 tutors)
            ['first_name' => 'Christopher', 'last_name' => 'Mendoza', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Jessica', 'last_name' => 'Castillo', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Daniel', 'last_name' => 'Morales', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Sarah', 'last_name' => 'Alvarez', 'account' => 'Tutlo', 'status' => 'inactive'],
            ['first_name' => 'Matthew', 'last_name' => 'Navarro', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Karen', 'last_name' => 'Delgado', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Joseph', 'last_name' => 'Herrera', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Nancy', 'last_name' => 'Jimenez', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Thomas', 'last_name' => 'Ramos', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Betty', 'last_name' => 'Salazar', 'account' => 'Tutlo', 'status' => 'active'],
            ['first_name' => 'Charles', 'last_name' => 'Ortega', 'account' => 'Tutlo', 'status' => 'inactive'],
            ['first_name' => 'Helen', 'last_name' => 'Vargas', 'account' => 'Tutlo', 'status' => 'active'],
            
            // Babilala Tutors (12 tutors)
            ['first_name' => 'Steven', 'last_name' => 'Castro', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Sandra', 'last_name' => 'Gutierrez', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Paul', 'last_name' => 'Ortiz', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Donna', 'last_name' => 'Rojas', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Mark', 'last_name' => 'Silva', 'account' => 'Babilala', 'status' => 'inactive'],
            ['first_name' => 'Carol', 'last_name' => 'Campos', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'George', 'last_name' => 'Medina', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Michelle', 'last_name' => 'Aguilar', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Kenneth', 'last_name' => 'Diaz', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Emily', 'last_name' => 'Suarez', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Edward', 'last_name' => 'Pena', 'account' => 'Babilala', 'status' => 'active'],
            ['first_name' => 'Laura', 'last_name' => 'Vasquez', 'account' => 'Babilala', 'status' => 'inactive'],
            
            // Talk915 Tutors (12 tutors)
            ['first_name' => 'Brian', 'last_name' => 'Cordero', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Deborah', 'last_name' => 'Valencia', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Ronald', 'last_name' => 'Estrada', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Sharon', 'last_name' => 'Fuentes', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Anthony', 'last_name' => 'Santana', 'account' => 'Talk915', 'status' => 'inactive'],
            ['first_name' => 'Cynthia', 'last_name' => 'Nunez', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Kevin', 'last_name' => 'Acosta', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Melissa', 'last_name' => 'Paredes', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Jason', 'last_name' => 'Vega', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Stephanie', 'last_name' => 'Ibarra', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Jeffrey', 'last_name' => 'Maldonado', 'account' => 'Talk915', 'status' => 'active'],
            ['first_name' => 'Rebecca', 'last_name' => 'Velasquez', 'account' => 'Talk915', 'status' => 'inactive'],
        ];

        foreach ($tutorData as $index => $data) {
            $this->createTutor($data, $accounts[$data['account']], $index);
        }
        
        $this->command->info('Tutors seeded successfully!');
    }

    /**
     * Create a single tutor with all related data
     */
    private function createTutor($data, $account, $index)
    {
        // Generate unique email
        $baseEmail = strtolower($data['first_name'] . '.' . $data['last_name']);
        $email = $baseEmail . '@gmail.com';
        $counter = 1;
        while (Applicant::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@gmail.com';
            $counter++;
        }

        // Create applicant
        $applicant = Applicant::create([
            'first_name' => $data['first_name'],
            'middle_name' => $this->getRandomMiddleName(),
            'last_name' => $data['last_name'],
            'birth_date' => Carbon::now()->subYears(rand(24, 45))->subMonths(rand(1, 11))->format('Y-m-d'),
            'address' => $this->getRandomAddress(),
            'contact_number' => $this->generatePhoneNumber(),
            'email' => $email,
            'ms_teams' => strtolower($data['first_name'] . $data['last_name']) . rand(1, 999) . '@outlook.com',
            'interview_time' => Carbon::now()->subDays(rand(30, 180)),
        ]);

        // Create qualification (only use existing columns)
        Qualification::create([
            'applicant_id' => $applicant->applicant_id,
            'education' => $this->getRandomEducation(),
            'esl_experience' => $this->getRandomESLExperience(),
        ]);

        // Create requirement (only use existing columns)
        Requirement::create([
            'applicant_id' => $applicant->applicant_id,
            'resume_link' => 'https://drive.google.com/file/' . uniqid(),
            'intro_video' => 'https://youtube.com/watch?v=' . uniqid(),
            'work_type' => $this->getRandomWorkSetup(),
            'speedtest' => rand(25, 100) . ' Mbps',
            'main_devices' => $this->getRandomComputerSpecs(),
            'backup_devices' => $this->getRandomBackupDevice(),
        ]);

        // Generate username and email
        $username = Tutor::generateUsername($data['first_name'], $data['last_name']);
        $email = Tutor::generateCompanyEmail($username);

        // Create tutor
        $tutor = Tutor::create([
            'applicant_id' => $applicant->applicant_id,
            'account_id' => $account->account_id,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password123'), // Default password
            'status' => $data['status'],
        ]);
        
        // Update hired_date_time separately
        DB::table('tutor')
            ->where('tutor_id', $tutor->tutor_id)
            ->update(['hired_date_time' => Carbon::now()->subDays(rand(15, 150))]);

        // Create work preferences for this tutor's applicant
        WorkPreference::create([
            'applicant_id' => $applicant->applicant_id,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'timezone' => 'Asia/Manila',
            'days_available' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'platform' => json_encode(['Zoom', 'Google Meet']),
            'can_teach' => json_encode(['English', 'Math', 'Science']),
        ]);
    }

    /**
     * Seed supervisors
     */
    private function seedSupervisors()
    {
        $this->command->info('Seeding supervisors...');
        
        $supervisorData = [
            ['first_name' => 'Angela', 'middle_name' => 'Rose', 'last_name' => 'Thompson', 'assigned_account' => 'GLS', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'active'],
            ['first_name' => 'Marcus', 'middle_name' => 'James', 'last_name' => 'Williams', 'assigned_account' => 'GLS', 'start_time' => '17:00:00', 'end_time' => '02:00:00', 'status' => 'active'],
            ['first_name' => 'Catherine', 'middle_name' => 'Marie', 'last_name' => 'Johnson', 'assigned_account' => 'GLS', 'start_time' => '22:00:00', 'end_time' => '07:00:00', 'status' => 'active'],
            ['first_name' => 'Raymond', 'middle_name' => 'Paul', 'last_name' => 'Brown', 'assigned_account' => 'Tutlo', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'active'],
            ['first_name' => 'Victoria', 'middle_name' => 'Lynn', 'last_name' => 'Davis', 'assigned_account' => 'Tutlo', 'start_time' => '17:00:00', 'end_time' => '02:00:00', 'status' => 'active'],
            ['first_name' => 'Harold', 'middle_name' => 'Lee', 'last_name' => 'Miller', 'assigned_account' => 'Tutlo', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'inactive'],
            ['first_name' => 'Samantha', 'middle_name' => 'Grace', 'last_name' => 'Wilson', 'assigned_account' => 'Babilala', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'active'],
            ['first_name' => 'Vincent', 'middle_name' => 'Alexander', 'last_name' => 'Moore', 'assigned_account' => 'Babilala', 'start_time' => '17:00:00', 'end_time' => '02:00:00', 'status' => 'active'],
            ['first_name' => 'Natalie', 'middle_name' => 'Ann', 'last_name' => 'Taylor', 'assigned_account' => 'Babilala', 'start_time' => '22:00:00', 'end_time' => '07:00:00', 'status' => 'active'],
            ['first_name' => 'Gregory', 'middle_name' => 'Scott', 'last_name' => 'Anderson', 'assigned_account' => 'Talk915', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'active'],
            ['first_name' => 'Christina', 'middle_name' => 'Joy', 'last_name' => 'Thomas', 'assigned_account' => 'Talk915', 'start_time' => '17:00:00', 'end_time' => '02:00:00', 'status' => 'active'],
            ['first_name' => 'Douglas', 'middle_name' => 'Ray', 'last_name' => 'Jackson', 'assigned_account' => 'Talk915', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'status' => 'inactive'],
        ];

        foreach ($supervisorData as $data) {
            // Generate unique email for supervisor
            $baseEmail = strtolower($data['first_name'] . '.' . $data['last_name']);
            $email = $baseEmail . '@ogsconnect.com';
            $counter = 1;
            while (Supervisor::where('email', $email)->exists()) {
                $email = $baseEmail . $counter . '@ogsconnect.com';
                $counter++;
            }

            Supervisor::create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'birth_date' => Carbon::now()->subYears(rand(28, 50))->subMonths(rand(1, 11))->format('Y-m-d'),
                'email' => $email,
                'contact_number' => $this->generatePhoneNumber(),
                'assigned_account' => $data['assigned_account'],
                'ms_teams' => strtolower($data['first_name'] . $data['last_name']) . rand(1, 999) . '@ogsconnect.com',
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'days_available' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'timezone' => 'Asia/Manila',
                'status' => $data['status'],
            ]);
        }
        
        $this->command->info('Supervisors seeded successfully!');
    }

    // Helper methods for generating realistic data
    
    private function getRandomMiddleName()
    {
        $middleNames = ['Mae', 'Ann', 'Grace', 'Joy', 'Rose', 'Faith', 'Hope', 'Marie', 'Lee', 'James', 'John', 'Paul', 'Mark', 'Luke'];
        return $middleNames[array_rand($middleNames)];
    }

    private function getRandomAddress()
    {
        $streets = ['Maple Street', 'Oak Avenue', 'Pine Road', 'Cedar Lane', 'Elm Drive', 'Birch Boulevard', 'Willow Way', 'Cherry Court'];
        $cities = ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Mandaluyong', 'Caloocan', 'Las Piñas'];
        $number = rand(100, 999);
        return $number . ' ' . $streets[array_rand($streets)] . ', ' . $cities[array_rand($cities)];
    }

    private function generatePhoneNumber()
    {
        $prefixes = ['0917', '0918', '0919', '0920', '0921', '0922', '0923', '0925', '0927', '0928'];
        return $prefixes[array_rand($prefixes)] . rand(1000000, 9999999);
    }

    private function getRandomEducation()
    {
        $education = [
            "Bachelor's Degree in Education",
            "Bachelor's Degree in English",
            "Bachelor's Degree in Communication",
            "Bachelor's Degree in Psychology",
            "Master's Degree in Education",
            "Bachelor's Degree in Liberal Arts",
        ];
        return $education[array_rand($education)];
    }

    private function getRandomESLExperience()
    {
        $experiences = [
            'No experience',
            '1-2 years',
            '2-3 years',
            '3-5 years',
            '5+ years',
        ];
        return $experiences[array_rand($experiences)];
    }

    private function getRandomWorkSetup()
    {
        $setups = ['Work from Home', 'Office-based', 'Hybrid'];
        return $setups[array_rand($setups)];
    }

    private function getRandomComputerSpecs()
    {
        $specs = [
            'Desktop - Intel Core i5, 8GB RAM, Windows 10',
            'Laptop - Intel Core i7, 16GB RAM, Windows 11',
            'Desktop - AMD Ryzen 5, 8GB RAM, Windows 10',
            'Laptop - Intel Core i3, 8GB RAM, Windows 10',
        ];
        return $specs[array_rand($specs)];
    }

    private function getRandomBackupDevice()
    {
        $devices = [
            'Laptop - Intel Core i3, 4GB RAM',
            'Tablet - iPad',
            'Desktop - Intel Core i5, 8GB RAM',
            'None',
        ];
        return $devices[array_rand($devices)];
    }

    private function getRandomAvailableDays()
    {
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $numDays = rand(5, 7);
        shuffle($allDays);
        return array_slice($allDays, 0, $numDays);
    }

    private function getRandomAvailableTimes()
    {
        $times = [
            ['start' => '08:00', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '17:00'],
            ['start' => '18:00', 'end' => '22:00'],
        ];
        return [$times[array_rand($times)]];
    }

    private function getRandomPreferredTimeRange()
    {
        $ranges = ['Morning (8AM-12PM)', 'Afternoon (1PM-5PM)', 'Evening (6PM-10PM)', 'Flexible'];
        return $ranges[array_rand($ranges)];
    }

    private function getRandomCompanyNotes($status)
    {
        if ($status === 'inactive') {
            $notes = [
                'Temporarily unavailable due to personal reasons',
                'On leave - returning next month',
                'Performance review pending',
            ];
        } else {
            $notes = [
                'Excellent performance, highly recommended',
                'Good communication skills with students',
                'Reliable and punctual',
                'Creative teaching methods',
                'Strong student engagement',
            ];
        }
        return $notes[array_rand($notes)];
    }
}
