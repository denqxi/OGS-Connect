<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        // Check if supervisors already exist
        if (DB::table('supervisors')->count() > 0) {
            if ($this->command) {
                $this->command->info('Supervisors already exist, skipping...');
            }
            return;
        }

        // Insert supervisors with formatted IDs as primary keys
        DB::table('supervisors')->insert([
            [
                'supID' => 'OGS-S1001',
                'sfname' => 'Admin',
                'smname' => 'A',
                'slname' => 'Supervisor',
                'semail' => 'admin@ogsconnect.com',
                'sconNum' => '09171234567',
                'birth_date' => '1985-03-15',
                'assigned_account' => 'GLS',
                'srole' => 'GLS Supervisor',
                'sshift' => 'Day Shift (7:00 AM - 3:30 PM)',
                'steams' => 'admin.supervisor@ogsconnect.com',
                'status' => 'active',
                'password' => bcrypt('admin3214'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supID' => 'OGS-S1002',
                'sfname' => 'Jane',
                'smname' => 'B',
                'slname' => 'Smith',
                'semail' => 'jane.smith@ogsconnect.com',
                'sconNum' => '09179876543',
                'birth_date' => '1990-07-22',
                'assigned_account' => 'Babilala',
                'srole' => 'Babilala Supervisor',
                'sshift' => 'Evening Shift (8:00 PM - 10:00 PM)',
                'steams' => 'jane.smith@ogsconnect.com',
                'status' => 'active',
                'password' => bcrypt('admin3214'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supID' => 'OGS-S1003',
                'sfname' => 'Michael',
                'smname' => 'C',
                'slname' => 'Johnson',
                'semail' => 'michael.johnson@ogsconnect.com',
                'sconNum' => '09175551234',
                'birth_date' => '1988-11-10',
                'assigned_account' => 'GLS',
                'srole' => 'GLS Supervisor',
                'sshift' => 'Day Shift (7:00 AM - 3:30 PM)',
                'steams' => 'michael.johnson@ogsconnect.com',
                'status' => 'active',
                'password' => bcrypt('admin3214'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Created 3 complete supervisor accounts for testing');
        $this->command->info('   - Admin A Supervisor (admin@ogsconnect.com) - ID: OGS-S1001 - Assigned: GLS - Role: GLS Supervisor');
        $this->command->info('   - Jane B Smith (jane.smith@ogsconnect.com) - ID: OGS-S1002 - Assigned: Babilala - Role: Babilala Supervisor');
        $this->command->info('   - Michael C Johnson (michael.johnson@ogsconnect.com) - ID: OGS-S1003 - Assigned: GLS - Role: GLS Supervisor');
        $this->command->info('   - Password for all: admin3214');
        $this->command->info('   - All fields populated: birth dates, roles, shifts, MS Teams accounts');
    }
}
