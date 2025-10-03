<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSupervisorColumns extends Command
{
    protected $signature = 'supervisors:check-columns';
    protected $description = 'Check supervisor table columns';

    public function handle()
    {
        $this->info('Supervisor table columns:');
        $this->line('========================');
        
        $columns = DB::select('DESCRIBE supervisors');
        foreach($columns as $column) {
            $this->line("{$column->Field} - {$column->Type}");
        }
    }
}