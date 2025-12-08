<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tutor;
use App\Models\Supervisor;
use DB;

class DebugPayrollAccess extends Command
{
    protected $signature = 'debug:payroll-access {supervisor_email} {tutor_id}';

    protected $description = 'Debug payroll access for a supervisor and tutor';

    public function handle()
    {
        $supervisorEmail = $this->argument('supervisor_email');
        $tutorID = $this->argument('tutor_id');

        $supervisor = Supervisor::where('email', $supervisorEmail)->first();
        if (!$supervisor) {
            $this->error("Supervisor not found: {$supervisorEmail}");
            return;
        }

        $tutor = Tutor::where('tutorID', $tutorID)->with(['account', 'applicant'])->first();
        if (!$tutor) {
            $this->error("Tutor not found: {$tutorID}");
            return;
        }

        $this->info('=== DEBUG PAYROLL ACCESS ===');
        $this->newLine();
        
        $this->info('SUPERVISOR:');
        $this->line("  Email: {$supervisor->email}");
        $this->line("  Assigned Account: '{$supervisor->assigned_account}' (Type: " . gettype($supervisor->assigned_account) . ")");
        $this->line("  Assigned Account Length: " . strlen($supervisor->assigned_account));
        
        $this->newLine();
        $this->info('TUTOR:');
        $this->line("  ID: {$tutor->tutorID}");
        $this->line("  Name: {$tutor->applicant->first_name} {$tutor->applicant->last_name}");
        $this->line("  Account ID: {$tutor->account_id}");
        
        if ($tutor->account) {
            $this->line("  Account Name: '{$tutor->account->account_name}' (Type: " . gettype($tutor->account->account_name) . ")");
            $this->line("  Account Name Length: " . strlen($tutor->account->account_name));
        } else {
            $this->line("  Account: NOT LOADED");
        }
        
        $this->newLine();
        $this->info('COMPARISON:');
        
        if ($supervisor->assigned_account && $tutor->account) {
            $match = $supervisor->assigned_account === $tutor->account->account_name;
            $this->line("  Supervisor assigned_account === Tutor account_name: " . ($match ? 'TRUE' : 'FALSE'));
            
            if (!$match) {
                $this->warn("  String comparison failed!");
                $this->line("  Supervisor: '" . $supervisor->assigned_account . "'");
                $this->line("  Tutor: '" . $tutor->account->account_name . "'");
                
                // Try case-insensitive comparison
                $caseInsensitiveMatch = strtolower($supervisor->assigned_account) === strtolower($tutor->account->account_name);
                $this->line("  Case-insensitive match: " . ($caseInsensitiveMatch ? 'TRUE' : 'FALSE'));
            }
        } else {
            $this->error("  Cannot compare - missing data!");
            $this->line("  Supervisor assigned_account: " . ($supervisor->assigned_account ? 'SET' : 'NULL'));
            $this->line("  Tutor account: " . ($tutor->account ? 'LOADED' : 'NULL'));
        }
    }
}
