<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutor;
use App\Models\TutorAccount;

class TutorAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating tutor accounts for all companies...');
        
        // Company configurations with time restrictions
        $companies = [
            'GLS' => [
                'restricted_start_time' => '07:00:00',
                'restricted_end_time' => '15:30:00',
                'company_notes' => 'GLS operates from 7:00 AM to 3:30 PM only',
                'preferred_time_range' => 'morning'
            ],
            'Babilala' => [
                'restricted_start_time' => '20:00:00',
                'restricted_end_time' => '22:00:00',
                'company_notes' => 'Babilala operates from 8:00 PM to 10:00 PM only',
                'preferred_time_range' => 'evening'
            ],
            'Tutlo' => [
                'restricted_start_time' => null,
                'restricted_end_time' => null,
                'company_notes' => 'Tutlo has open hours - no time restrictions',
                'preferred_time_range' => 'flexible'
            ],
            'Talk915' => [
                'restricted_start_time' => null,
                'restricted_end_time' => null,
                'company_notes' => 'Talk915 has open hours - no time restrictions',
                'preferred_time_range' => 'flexible'
            ]
        ];

        $tutors = Tutor::all();
        $accountCount = 0;
        $usedGlsIds = []; // Track used GLS IDs to avoid duplicates

        foreach ($tutors as $tutor) {
            // Determine which companies this tutor should have accounts for
            $assignedCompanies = $this->getAssignedCompaniesForTutor($tutor);
            
            foreach ($assignedCompanies as $companyName) {
                $config = $companies[$companyName];
                
                // Get personalized availability for this tutor and company
                $personalizedAvailability = $this->getPersonalizedAvailability($tutor, $companyName, $config);
                
                // Prepare base account data
                $accountData = [
                    'tutor_id' => $tutor->tutorID,
                    'account_name' => $companyName,
                    'available_days' => json_encode($personalizedAvailability['days']),
                    'available_times' => json_encode($personalizedAvailability['times']),
                    'preferred_time_range' => $config['preferred_time_range'],
                    'timezone' => 'UTC',
                    'restricted_start_time' => $config['restricted_start_time'],
                    'restricted_end_time' => $config['restricted_end_time'],
                    'company_notes' => $config['company_notes'],
                    'availability_notes' => $personalizedAvailability['notes'],
                    'status' => 'active'
                ];

                // Add account-specific fields
                if ($companyName === 'GLS') {
                    // Generate random GLS ID between 100-9999 (hundreds to thousands)
                    do {
                        $glsId = rand(100, 9999);
                    } while (in_array($glsId, $usedGlsIds));
                    
                    $usedGlsIds[] = $glsId;
                    $accountData['gls_id'] = (string)$glsId;
                    $accountData['account_number'] = (string)$glsId; // Use GLS ID as account number
                    $accountData['username'] = 'OGS-' . strtolower($tutor->first_name); // GLS username is OGS-{FirstName}
                    $accountData['screen_name'] = 'OGS-' . $tutor->first_name; // GLS screen_name is OGS-{FirstName}
                } elseif ($companyName === 'Babilala') {
                    $accountData['username'] = strtolower($tutor->first_name) . '.' . strtolower($tutor->last_name); // Babilala username is firstname.lastname
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name; // Babilala screen_name is full name
                    $accountData['account_number'] = 'BAB-' . rand(100, 999) . '-' . rand(100, 999); // Babilala account number format
                } elseif ($companyName === 'Tutlo') {
                    $accountData['username'] = 'tutlo_' . strtolower($tutor->first_name) . strtolower(substr($tutor->last_name, 0, 3)); // Tutlo username is tutlo_firstname+3letters
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name; // Tutlo screen_name is full name
                    $accountData['account_number'] = 'TUT-' . rand(100000, 999999); // Tutlo account number format
                } elseif ($companyName === 'Talk915') {
                    $accountData['username'] = strtolower($tutor->first_name) . strtolower(substr($tutor->last_name, 0, 2)); // Talk915 username is firstname+2letters
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name; // Talk915 screen_name is full name
                    $accountData['account_number'] = 'T915-' . rand(10000, 99999); // Talk915 account number format
                }

                TutorAccount::create($accountData);
                $accountCount++;
            }
        }

        $this->command->info("✅ Created {$accountCount} tutor accounts across 4 companies");
        $this->command->info("   - GLS: 7:00 AM - 3:30 PM (Morning hours) - All tutors");
        $this->command->info("   - Babilala: 8:00 PM - 10:00 PM (Evening hours) - Some tutors");
        $this->command->info("   - Tutlo: Open hours (Flexible) - Some tutors");
        $this->command->info("   - Talk915: Open hours (Flexible) - Some tutors");
    }

    /**
     * Determine which companies a tutor should have accounts for
     * This creates selective assignment instead of giving all tutors all accounts
     */
    private function getAssignedCompaniesForTutor($tutor): array
    {
        // Create a seed based on tutor ID for consistent assignment
        $seed = crc32($tutor->tutorID);
        mt_srand($seed);
        
        // All available companies
        $allCompanies = ['GLS', 'Babilala', 'Tutlo', 'Talk915'];
        
        // Every tutor gets GLS account (primary account)
        $assignedCompanies = ['GLS'];
        
        // Randomly assign 1-3 additional companies (some tutors, not all)
        $numAdditionalCompanies = mt_rand(1, 3);
        $availableCompanies = array_diff($allCompanies, $assignedCompanies);
        
        for ($i = 0; $i < $numAdditionalCompanies && !empty($availableCompanies); $i++) {
            $randomIndex = mt_rand(0, count($availableCompanies) - 1);
            $selectedCompany = array_values($availableCompanies)[$randomIndex];
            $assignedCompanies[] = $selectedCompany;
            
            // Remove selected company from available options
            $availableCompanies = array_diff($availableCompanies, [$selectedCompany]);
        }
        
        return $assignedCompanies;
    }

    /**
     * Get personalized availability for a tutor based on company rules and tutor preferences
     */
    private function getPersonalizedAvailability($tutor, string $companyName, array $config): array
    {
        // Create a more varied seed based on tutor ID and company name
        $seed = crc32($tutor->tutorID . $companyName . $tutor->first_name);
        mt_srand($seed); // Use mt_srand for better randomness
        
        switch ($companyName) {
            case 'GLS':
                return $this->getGlsAvailability($tutor, $seed, $config);
            case 'Babilala':
                return $this->getBabilalaAvailability($tutor, $seed, $config);
            case 'Tutlo':
                return $this->getTutloAvailability($tutor, $seed, $config);
            case 'Talk915':
                return $this->getTalk915Availability($tutor, $seed, $config);
            default:
                return $this->getDefaultAvailability($tutor, $seed, $config);
        }
    }

    /**
     * GLS availability: No weekends, 7:00 AM - 3:30 PM, varied day preferences
     */
    private function getGlsAvailability($tutor, $seed, array $config): array
    {
        // All possible weekdays for GLS (no weekends)
        $allWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Randomly select 2-5 weekdays for this tutor
        $numDays = mt_rand(2, 5);
        $selectedDays = [];
        $availableDays = $allWeekdays;
        
        for ($i = 0; $i < $numDays; $i++) {
            $randomIndex = mt_rand(0, count($availableDays) - 1);
            $selectedDays[] = $availableDays[$randomIndex];
            array_splice($availableDays, $randomIndex, 1); // Remove selected day
        }
        
        sort($selectedDays); // Sort for consistent display
        
        // All possible GLS time slots (7:00 AM - 3:30 PM)
        $allTimeSlots = [
            '07:00-08:00', '08:00-09:00', '09:00-10:00', '10:00-11:00',
            '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-15:30'
        ];
        
        // Create day-specific times (can vary per day)
        $times = [];
        foreach ($selectedDays as $day) {
            // Randomly select 3-8 time slots for this day
            $numSlots = mt_rand(3, 8);
            $dayTimes = [];
            $availableSlots = $allTimeSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1); // Remove selected slot
            }
            
            sort($dayTimes); // Sort for consistent display
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "GLS account for {$tutor->first_name} - Available weekdays only, within 7:00 AM - 3:30 PM. Prefers " . implode(', ', $selectedDays) . "."
        ];
    }

    /**
     * Babilala availability: Evening only, 8:00 PM - 10:00 PM
     */
    private function getBabilalaAvailability($tutor, $seed, array $config): array
    {
        // All possible weekdays for Babilala (no weekends typically)
        $allWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Randomly select 2-5 weekdays for this tutor
        $numDays = mt_rand(2, 5);
        $selectedDays = [];
        $availableDays = $allWeekdays;
        
        for ($i = 0; $i < $numDays; $i++) {
            $randomIndex = mt_rand(0, count($availableDays) - 1);
            $selectedDays[] = $availableDays[$randomIndex];
            array_splice($availableDays, $randomIndex, 1); // Remove selected day
        }
        
        sort($selectedDays); // Sort for consistent display
        
        // Babilala has limited evening hours (8:00 PM - 10:00 PM)
        $eveningSlots = ['20:00-21:00', '21:00-22:00'];
        
        // Create day-specific times (can vary per day)
        $times = [];
        foreach ($selectedDays as $day) {
            // Randomly select 1-2 evening slots for this day
            $numSlots = mt_rand(1, 2);
            $dayTimes = [];
            $availableSlots = $eveningSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1); // Remove selected slot
            }
            
            sort($dayTimes); // Sort for consistent display
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "Babilala account for {$tutor->first_name} - Evening hours only (8:00 PM - 10:00 PM). Available on " . implode(', ', $selectedDays) . "."
        ];
    }

    /**
     * Tutlo availability: Flexible hours, varied preferences
     */
    private function getTutloAvailability($tutor, $seed, array $config): array
    {
        // All possible days for Tutlo (including weekends)
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Randomly select 3-7 days for this tutor
        $numDays = mt_rand(3, 7);
        $selectedDays = [];
        $availableDays = $allDays;
        
        for ($i = 0; $i < $numDays; $i++) {
            $randomIndex = mt_rand(0, count($availableDays) - 1);
            $selectedDays[] = $availableDays[$randomIndex];
            array_splice($availableDays, $randomIndex, 1); // Remove selected day
        }
        
        sort($selectedDays); // Sort for consistent display
        
        // All possible flexible time slots for Tutlo
        $allTimeSlots = [
            '08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00',
            '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00',
            '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00'
        ];
        
        // Create day-specific times (can vary per day)
        $times = [];
        foreach ($selectedDays as $day) {
            // Randomly select 2-6 time slots for this day
            $numSlots = mt_rand(2, 6);
            $dayTimes = [];
            $availableSlots = $allTimeSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1); // Remove selected slot
            }
            
            sort($dayTimes); // Sort for consistent display
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "Tutlo account for {$tutor->first_name} - Flexible hours. Available on " . implode(', ', $selectedDays) . " with varied time slots."
        ];
    }

    /**
     * Talk915 availability: Flexible hours, varied preferences
     */
    private function getTalk915Availability($tutor, $seed, array $config): array
    {
        // All possible days for Talk915 (including weekends)
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Randomly select 3-7 days for this tutor
        $numDays = mt_rand(3, 7);
        $selectedDays = [];
        $availableDays = $allDays;
        
        for ($i = 0; $i < $numDays; $i++) {
            $randomIndex = mt_rand(0, count($availableDays) - 1);
            $selectedDays[] = $availableDays[$randomIndex];
            array_splice($availableDays, $randomIndex, 1); // Remove selected day
        }
        
        sort($selectedDays); // Sort for consistent display
        
        // All possible flexible time slots for Talk915
        $allTimeSlots = [
            '07:00-08:00', '08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00',
            '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00',
            '17:00-18:00', '18:00-19:00', '19:00-20:00', '20:00-21:00', '21:00-22:00'
        ];
        
        // Create day-specific times (can vary per day)
        $times = [];
        foreach ($selectedDays as $day) {
            // Randomly select 2-7 time slots for this day
            $numSlots = mt_rand(2, 7);
            $dayTimes = [];
            $availableSlots = $allTimeSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1); // Remove selected slot
            }
            
            sort($dayTimes); // Sort for consistent display
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "Talk915 account for {$tutor->first_name} - Flexible hours. Available on " . implode(', ', $selectedDays) . " with varied time slots."
        ];
    }

    /**
     * Default availability pattern
     */
    private function getDefaultAvailability($tutor, $seed, array $config): array
    {
        // All possible weekdays for default
        $allWeekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Randomly select 3-5 weekdays for this tutor
        $numDays = mt_rand(3, 5);
        $selectedDays = [];
        $availableDays = $allWeekdays;
        
        for ($i = 0; $i < $numDays; $i++) {
            $randomIndex = mt_rand(0, count($availableDays) - 1);
            $selectedDays[] = $availableDays[$randomIndex];
            array_splice($availableDays, $randomIndex, 1); // Remove selected day
        }
        
        sort($selectedDays); // Sort for consistent display
        
        // Default time slots
        $defaultSlots = ['09:00-10:00', '10:00-11:00', '11:00-12:00', '14:00-15:00', '15:00-16:00', '16:00-17:00'];
        
        // Create day-specific times
        $times = [];
        foreach ($selectedDays as $day) {
            // Randomly select 3-5 time slots for this day
            $numSlots = mt_rand(3, 5);
            $dayTimes = [];
            $availableSlots = $defaultSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1); // Remove selected slot
            }
            
            sort($dayTimes); // Sort for consistent display
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "Default availability for {$tutor->first_name} - Available on " . implode(', ', $selectedDays) . "."
        ];
    }

    /**
     * Get default available times based on company restrictions (legacy method)
     */
    private function getDefaultTimes(string $companyName): array
    {
        switch ($companyName) {
            case 'GLS':
                return [
                    '07:00-08:00', '08:00-09:00', '09:00-10:00', '10:00-11:00',
                    '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-15:30'
                ];
            case 'Babilala':
                return [
                    '20:00-21:00', '21:00-22:00'
                ];
            case 'Tutlo':
            case 'Talk915':
                return [
                    '09:00-10:00', '10:00-11:00', '11:00-12:00', '14:00-15:00',
                    '15:00-16:00', '16:00-17:00', '19:00-20:00', '20:00-21:00'
                ];
            default:
                return ['09:00-17:00'];
        }
    }
}
