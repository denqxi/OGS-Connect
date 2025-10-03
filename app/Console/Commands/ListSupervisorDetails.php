<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supervisor;

class ListSupervisorDetails extends Command
{
    protected $signature = 'supervisors:details';
    protected $description = 'List all supervisors with detailed information';

    public function handle()
    {
        $supervisors = Supervisor::all();
        
        $this->info('Supervisor Details:');
        $this->line('==================');
        
        foreach ($supervisors as $supervisor) {
            $account = $supervisor->assigned_account ? $supervisor->assigned_account . ' Supervisor' : 'Unassigned';
            $this->line("{$supervisor->supID}: {$supervisor->sfname} {$supervisor->slname}");
            $this->line("  Role: {$account}");
            $this->line("  Email: {$supervisor->semail}");
            $this->line("  MS Teams: " . ($supervisor->steams ?? 'Not set'));
            $this->line("  Shift: " . ($supervisor->sshift ?? 'Not set'));
            $this->line("  Address: " . ($supervisor->saddress ?? 'Not set'));
            $this->line("  Employee ID: " . ($supervisor->employee_id ?? 'Not set'));
            $this->line("  Status: " . ($supervisor->status ?? 'Not set'));
            $this->line('');
        }
        
        $this->info("Total: {$supervisors->count()} supervisors");
    }
}