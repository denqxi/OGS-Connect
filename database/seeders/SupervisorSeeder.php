<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        // Check if supervisors already exist
        if (DB::table('supervisor')->count() > 0) {
            if ($this->command) {
                $this->command->info('Supervisors already exist, skipping...');
            }
            return;
        }

        // Create test supervisor with new normalized structure
        Supervisor::create([
            'supID' => 'OGS-S1001',
            'first_name' => 'Admin',
            'middle_name' => 'A',
            'last_name' => 'Supervisor',
            'email' => 'admin@ogsconnect.com',
            'contact_number' => '09171234567',
            'birth_date' => '1985-03-15',
            'assigned_account' => 'GLS',
            'ms_teams' => 'admin.supervisor@ogsconnect.com',
            'start_time' => '07:00:00',
            'end_time' => '15:30:00',
            'days_available' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'timezone' => 'Asia/Manila',
            'status' => 'active',
            'password' => 'admin3214', // Will be hashed automatically by the model
        ]);

        if ($this->command) {
            $this->command->info('✅ Created test supervisor account:');
            $this->command->info('   - Email/ID: admin@ogsconnect.com or OGS-S1001');
            $this->command->info('   - Password: admin3214');
            $this->command->info('   - Name: Admin A Supervisor');
            $this->command->info('   - Assigned Account: GLS');
        }
    }
}
