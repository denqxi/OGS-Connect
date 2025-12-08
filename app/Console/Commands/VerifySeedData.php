<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class VerifySeedData extends Command
{
    protected $signature = 'verify:seed-data';

    protected $description = 'Verify that supervisors and tutors are properly assigned to accounts';

    public function handle()
    {
        $this->info('=== SEEDING VERIFICATION ===');
        
        $this->info("\n📊 ACCOUNTS:");
        $accounts = DB::table('accounts')->select('account_id', 'account_name')->get();
        foreach ($accounts as $a) {
            $this->line("  ID: {$a->account_id} - {$a->account_name}");
        }

        $this->info("\n📋 SUPERVISORS BY ACCOUNT:");
        $supervisors = DB::table('supervisors')->select('supID', 'first_name', 'last_name', 'assigned_account')->get();
        foreach ($supervisors as $s) {
            $this->line("  {$s->supID} - {$s->first_name} {$s->last_name} ({$s->assigned_account})");
        }

        $this->info("\n👥 TUTORS BY ACCOUNT:");
        $tutors = DB::table('tutors')
            ->join('accounts', 'tutors.account_id', '=', 'accounts.account_id')
            ->join('applicants', 'tutors.applicant_id', '=', 'applicants.applicant_id')
            ->select('tutors.tutorID', 'applicants.first_name', 'applicants.last_name', 'accounts.account_name')
            ->orderBy('accounts.account_name')
            ->get();
        
        $currentAccount = null;
        foreach ($tutors as $t) {
            if ($currentAccount !== $t->account_name) {
                $currentAccount = $t->account_name;
                $this->line("\n  📌 {$currentAccount}:");
            }
            $this->line("    {$t->tutorID} - {$t->first_name} {$t->last_name}");
        }

        $this->info("\n✅ Verification complete!");
    }
}
