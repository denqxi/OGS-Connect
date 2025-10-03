<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;

class SupervisorAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisors = [
            [
                'supID' => 'OGS-S0001',
                'sfname' => 'Maria',
                'slname' => 'Santos',
                'semail' => 'maria.santos@ogsconnect.com',
                'sconNum' => '+639171234567',
                'password' => Hash::make('password123'),
                'assigned_account' => 'GLS',
                'srole' => 'GLS Supervisor',
                'saddress' => 'Manila, Philippines',
                'steams' => 'maria.santos@teams.com',
                'sshift' => 'Day Shift',
                'status' => 'active',
                'susername' => 'maria.santos'
            ],
            [
                'supID' => 'OGS-S0002',
                'sfname' => 'John',
                'slname' => 'Doe',
                'semail' => 'john.doe@ogsconnect.com',
                'sconNum' => '+639171234568',
                'password' => Hash::make('password123'),
                'assigned_account' => 'Tutlo',
                'srole' => 'Tutlo Supervisor',
                'saddress' => 'Cebu, Philippines',
                'steams' => 'john.doe@teams.com',
                'sshift' => 'Night Shift',
                'status' => 'active',
                'susername' => 'john.doe'
            ],
            [
                'supID' => 'OGS-S0003',
                'sfname' => 'Sarah',
                'slname' => 'Johnson',
                'semail' => 'sarah.johnson@ogsconnect.com',
                'sconNum' => '+639171234569',
                'password' => Hash::make('password123'),
                'assigned_account' => 'Babilala',
                'srole' => 'Babilala Supervisor',
                'saddress' => 'Davao, Philippines',
                'steams' => 'sarah.johnson@teams.com',
                'sshift' => 'Day Shift',
                'status' => 'active',
                'susername' => 'sarah.johnson'
            ],
            [
                'supID' => 'OGS-S0004',
                'sfname' => 'Michael',
                'slname' => 'Chen',
                'semail' => 'michael.chen@ogsconnect.com',
                'sconNum' => '+639171234570',
                'password' => Hash::make('password123'),
                'assigned_account' => 'Talk915',
                'srole' => 'Talk915 Supervisor',
                'saddress' => 'Quezon City, Philippines',
                'steams' => 'michael.chen@teams.com',
                'sshift' => 'Evening Shift',
                'status' => 'active',
                'susername' => 'michael.chen'
            ]
        ];

        foreach ($supervisors as $supervisorData) {
            Supervisor::updateOrCreate(
                ['supID' => $supervisorData['supID']],
                $supervisorData
            );
        }

        $this->command->info('✅ Created supervisors with assigned accounts:');
        $this->command->info('   - Maria Santos (GLS Supervisor)');
        $this->command->info('   - John Doe (Tutlo Supervisor)');
        $this->command->info('   - Sarah Johnson (Babilala Supervisor)');
        $this->command->info('   - Michael Chen (Talk915 Supervisor)');
    }
}