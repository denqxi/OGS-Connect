<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MultiAccountSupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds to assign supervisors to different accounts
     */
    public function run(): void
    {
        // Define supervisor data for different accounts with fixed account names
        $supervisorsData = [
            [
                'account_name' => 'GLS',
                'first_name' => 'Robert',
                'middle_name' => 'M',
                'last_name' => 'Johnson',
                'email' => 'robert.johnson@ogsconnect.com',
                'contact_number' => '09171234567',
                'ms_teams' => 'robert.johnson@ogsconnect.com',
            ],
            [
                'account_name' => 'Tutlo',
                'first_name' => 'Jennifer',
                'middle_name' => 'K',
                'last_name' => 'Davis',
                'email' => 'jennifer.davis@ogsconnect.com',
                'contact_number' => '09174567890',
                'ms_teams' => 'jennifer.davis@ogsconnect.com',
            ],
            [
                'account_name' => 'Babilala',
                'first_name' => 'Thomas',
                'middle_name' => 'D',
                'last_name' => 'Brown',
                'email' => 'thomas.brown@ogsconnect.com',
                'contact_number' => '09173456789',
                'ms_teams' => 'thomas.brown@ogsconnect.com',
            ],
            [
                'account_name' => 'Talk915',
                'first_name' => 'Patricia',
                'middle_name' => 'A',
                'last_name' => 'Williams',
                'email' => 'patricia.williams@ogsconnect.com',
                'contact_number' => '09172345678',
                'ms_teams' => 'patricia.williams@ogsconnect.com',
            ],
        ];

        foreach ($supervisorsData as $supervisorData) {
            // Check if supervisor for this account already exists
            $existingSupervisor = Supervisor::where('assigned_account', $supervisorData['account_name'])
                ->where('email', $supervisorData['email'])
                ->first();

            if ($existingSupervisor) {
                if ($this->command) {
                    $this->command->info("Supervisor for {$supervisorData['account_name']} already exists, skipping...");
                }
                continue;
            }

            // Create supervisor
            $supervisor = Supervisor::create([
                'first_name' => $supervisorData['first_name'],
                'middle_name' => $supervisorData['middle_name'],
                'last_name' => $supervisorData['last_name'],
                'email' => $supervisorData['email'],
                'contact_number' => $supervisorData['contact_number'],
                'birth_date' => '1985-05-20',
                'assigned_account' => $supervisorData['account_name'],
                'ms_teams' => $supervisorData['ms_teams'],
                'shift' => 'day',
                'password' => 'supervisor1234', // Will be hashed automatically by the model
            ]);

            if ($this->command) {
                $this->command->info("✅ Created supervisor: {$supervisorData['first_name']} {$supervisorData['last_name']} ({$supervisor->supID}) for {$supervisorData['account_name']}");
            }
        }

        if ($this->command) {
            $this->command->info("\n✅ All supervisors have been assigned to their respective accounts!");
            $this->command->info("Total supervisors created: " . Supervisor::count());
        }
    }
}
