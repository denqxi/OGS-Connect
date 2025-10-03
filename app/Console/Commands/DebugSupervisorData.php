<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supervisor;

class DebugSupervisorData extends Command
{
    protected $signature = 'supervisors:debug';
    protected $description = 'Debug supervisor data to check field values';

    public function handle()
    {
        $this->info('Debugging Supervisor Data:');
        $this->line('========================');
        
        $supervisors = Supervisor::all();
        
        foreach ($supervisors as $supervisor) {
            $this->line("Supervisor: {$supervisor->supID} - {$supervisor->sfname} {$supervisor->slname}");
            $this->line("  supID: {$supervisor->supID}");
            $this->line("  employee_id: " . ($supervisor->employee_id ?? 'NULL'));
            $this->line("  susername: " . ($supervisor->susername ?? 'NULL'));
            $this->line("  Raw attributes:");
            
            $attributes = $supervisor->toArray();
            foreach ($attributes as $key => $value) {
                if (in_array($key, ['supID', 'employee_id', 'susername'])) {
                    $this->line("    {$key}: " . ($value ?? 'NULL'));
                }
            }
            $this->line('');
        }
        
        $this->info('Check Laravel logs for additional debug info when accessing supervisor profile.');
    }
}