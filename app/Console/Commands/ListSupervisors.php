<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supervisor;

class ListSupervisors extends Command
{
    protected $signature = 'supervisors:list';
    protected $description = 'List all supervisors with their assigned accounts';

    public function handle()
    {
        $supervisors = Supervisor::all();
        
        $this->info('Current Supervisors:');
        $this->line('==================');
        
        foreach ($supervisors as $supervisor) {
            $account = $supervisor->assigned_account ? $supervisor->assigned_account . ' Supervisor' : 'Unassigned';
            $this->line("{$supervisor->supID}: {$supervisor->sfname} {$supervisor->slname} - {$account}");
        }
        
        $this->line('');
        $this->info("Total: {$supervisors->count()} supervisors");
    }
}