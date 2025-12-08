<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ListSupervisorCredentials extends Command
{
    protected $signature = 'supervisors:list-credentials';

    protected $description = 'Display email and password for all supervisors';

    public function handle()
    {
        $this->info('=== SUPERVISOR CREDENTIALS ===');
        $this->newLine();
        
        $supervisors = DB::table('supervisors')
            ->select('supID', 'first_name', 'last_name', 'email', 'assigned_account')
            ->orderBy('assigned_account')
            ->get();

        $this->table(
            ['ID', 'Name', 'Email', 'Assigned Account', 'Password'],
            collect($supervisors)->map(function($s) {
                return [
                    $s->supID,
                    $s->first_name . ' ' . $s->last_name,
                    $s->email,
                    $s->assigned_account,
                    'supervisor1234'
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('Note: All supervisors use the default password: supervisor1234');
    }
}
