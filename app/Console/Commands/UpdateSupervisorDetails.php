<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supervisor;

class UpdateSupervisorDetails extends Command
{
    protected $signature = 'supervisors:update-details';
    protected $description = 'Update MS Teams and shift details for existing supervisors';

    public function handle()
    {
        $this->info('Updating supervisor details...');
        
        // Update OGS-S1001 (Admin Supervisor)
        $adminSupervisor = Supervisor::where('supID', 'OGS-S1001')->first();
        if ($adminSupervisor) {
            $adminSupervisor->update([
                'steams' => 'admin.supervisor@teams.com',
                'sshift' => 'Day Shift',
                'saddress' => 'Manila, Philippines',
                'srole' => 'GLS Supervisor'
            ]);
            $this->line("✓ Updated OGS-S1001: {$adminSupervisor->sfname} {$adminSupervisor->slname}");
            $this->line("  - MS Teams: admin.supervisor@teams.com");
            $this->line("  - Shift: Day Shift");
            $this->line("  - Address: Manila, Philippines");
        } else {
            $this->error("OGS-S1001 not found");
        }
        
        // Update OGS-S1002 (Jane Smith)
        $janeSupervisor = Supervisor::where('supID', 'OGS-S1002')->first();
        if ($janeSupervisor) {
            $janeSupervisor->update([
                'steams' => 'jane.smith@teams.com',
                'sshift' => 'Night Shift',
                'saddress' => 'Cebu, Philippines',
                'srole' => 'GLS Supervisor'
            ]);
            $this->line("✓ Updated OGS-S1002: {$janeSupervisor->sfname} {$janeSupervisor->slname}");
            $this->line("  - MS Teams: jane.smith@teams.com");
            $this->line("  - Shift: Night Shift");
            $this->line("  - Address: Cebu, Philippines");
        } else {
            $this->error("OGS-S1002 not found");
        }
        
        $this->info('Supervisor details updated successfully!');
    }
}