<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        // Insert supervisors with formatted IDs as primary keys
        DB::table('supervisors')->insert([
            [
                'supID' => 'OGS-S1001',
                'sfname' => 'Admin',
                'smname' => 'A',
                'slname' => 'Supervisor',
                'semail' => 'admin@ogsconnect.com',
                'sconNum' => '09171234567',
                'password' => bcrypt('password123'),
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
                'password' => bcrypt('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Created 2 supervisor accounts for testing');
        $this->command->info('   - Admin A Supervisor (admin@ogsconnect.com) - ID: OGS-S1001');
        $this->command->info('   - Jane B Smith (jane.smith@ogsconnect.com) - ID: OGS-S1002');
        $this->command->info('   - Password for both: password123');
    }
}
