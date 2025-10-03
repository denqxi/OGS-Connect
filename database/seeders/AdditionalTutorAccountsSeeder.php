<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutor;
use App\Models\TutorAccount;

class AdditionalTutorAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Adding additional tutor accounts for existing tutors...');
        
        // Company configurations with time restrictions
        $companies = [
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

        foreach ($tutors as $tutor) {
            // Get existing accounts for this tutor
            $existingAccounts = $tutor->accounts->pluck('account_name')->toArray();
            
            // Determine which additional companies this tutor should have accounts for
            $additionalCompanies = $this->getAdditionalCompaniesForTutor($tutor, $existingAccounts);
            
            foreach ($additionalCompanies as $companyName) {
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
                if ($companyName === 'Babilala') {
                    $accountData['username'] = strtolower($tutor->first_name) . '.' . strtolower($tutor->last_name);
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name;
                    $accountData['account_number'] = 'BAB-' . rand(100, 999) . '-' . rand(100, 999);
                } elseif ($companyName === 'Tutlo') {
                    $accountData['username'] = 'tutlo_' . strtolower($tutor->first_name) . strtolower(substr($tutor->last_name, 0, 3));
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name;
                    $accountData['account_number'] = 'TUT-' . rand(100000, 999999);
                } elseif ($companyName === 'Talk915') {
                    $accountData['username'] = strtolower($tutor->first_name) . strtolower(substr($tutor->last_name, 0, 2));
                    $accountData['screen_name'] = $tutor->first_name . ' ' . $tutor->last_name;
                    $accountData['account_number'] = 'T915-' . rand(10000, 99999);
                }

                TutorAccount::create($accountData);
                $accountCount++;
            }
        }

        $this->command->info("✅ Added {$accountCount} additional tutor accounts");
        $this->command->info("   - Babilala: 8:00 PM - 10:00 PM (Evening hours)");
        $this->command->info("   - Tutlo: Open hours (Flexible)");
        $this->command->info("   - Talk915: Open hours (Flexible)");
    }

    /**
     * Determine which additional companies a tutor should have accounts for
     */
    private function getAdditionalCompaniesForTutor($tutor, array $existingAccounts): array
    {
        // Create a seed based on tutor ID for consistent assignment
        $seed = crc32($tutor->tutorID . 'additional');
        mt_srand($seed);
        
        // Available companies (excluding GLS since all tutors already have it)
        $availableCompanies = ['Babilala', 'Tutlo', 'Talk915'];
        
        // Filter out companies the tutor already has
        $availableCompanies = array_diff($availableCompanies, $existingAccounts);
        
        // Randomly assign 1-2 additional companies
        $numAdditionalCompanies = mt_rand(1, min(2, count($availableCompanies)));
        $assignedCompanies = [];
        
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
        $seed = crc32($tutor->tutorID . $companyName . $tutor->first_name . 'additional');
        mt_srand($seed);
        
        switch ($companyName) {
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
            array_splice($availableDays, $randomIndex, 1);
        }
        
        sort($selectedDays);
        
        // Babilala has limited evening hours (8:00 PM - 10:00 PM)
        $eveningSlots = ['20:00-21:00', '21:00-22:00'];
        
        // Create day-specific times
        $times = [];
        foreach ($selectedDays as $day) {
            $numSlots = mt_rand(1, 2);
            $dayTimes = [];
            $availableSlots = $eveningSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1);
            }
            
            sort($dayTimes);
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
            array_splice($availableDays, $randomIndex, 1);
        }
        
        sort($selectedDays);
        
        // All possible flexible time slots for Tutlo
        $allTimeSlots = [
            '08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00',
            '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00',
            '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00'
        ];
        
        // Create day-specific times
        $times = [];
        foreach ($selectedDays as $day) {
            $numSlots = mt_rand(2, 6);
            $dayTimes = [];
            $availableSlots = $allTimeSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1);
            }
            
            sort($dayTimes);
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
            array_splice($availableDays, $randomIndex, 1);
        }
        
        sort($selectedDays);
        
        // All possible flexible time slots for Talk915
        $allTimeSlots = [
            '07:00-08:00', '08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00',
            '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00',
            '17:00-18:00', '18:00-19:00', '19:00-20:00', '20:00-21:00', '21:00-22:00'
        ];
        
        // Create day-specific times
        $times = [];
        foreach ($selectedDays as $day) {
            $numSlots = mt_rand(2, 7);
            $dayTimes = [];
            $availableSlots = $allTimeSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1);
            }
            
            sort($dayTimes);
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
            array_splice($availableDays, $randomIndex, 1);
        }
        
        sort($selectedDays);
        
        // Default time slots
        $defaultSlots = ['09:00-10:00', '10:00-11:00', '11:00-12:00', '14:00-15:00', '15:00-16:00', '16:00-17:00'];
        
        // Create day-specific times
        $times = [];
        foreach ($selectedDays as $day) {
            $numSlots = mt_rand(3, 5);
            $dayTimes = [];
            $availableSlots = $defaultSlots;
            
            for ($i = 0; $i < $numSlots; $i++) {
                $randomIndex = mt_rand(0, count($availableSlots) - 1);
                $dayTimes[] = $availableSlots[$randomIndex];
                array_splice($availableSlots, $randomIndex, 1);
            }
            
            sort($dayTimes);
            $times[$day] = $dayTimes;
        }
        
        return [
            'days' => $selectedDays,
            'times' => $times,
            'notes' => "Default availability for {$tutor->first_name} - Available on " . implode(', ', $selectedDays) . "."
        ];
    }
}
