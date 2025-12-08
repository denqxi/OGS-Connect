<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ListTutorCredentials extends Command
{
    protected $signature = 'tutors:list-credentials';

    protected $description = 'Display email and password for all tutors';

    public function handle()
    {
        $this->info('=== TUTOR CREDENTIALS ===');
        $this->newLine();
        
        $tutors = DB::table('tutors')
            ->join('applicants', 'tutors.applicant_id', '=', 'applicants.applicant_id')
            ->join('accounts', 'tutors.account_id', '=', 'accounts.account_id')
            ->select('tutors.tutorID', 'applicants.first_name', 'applicants.last_name', 'tutors.email', 'accounts.account_name', 'tutors.username')
            ->orderBy('accounts.account_name')
            ->orderBy('applicants.first_name')
            ->get();

        $this->table(
            ['ID', 'Name', 'Username', 'Email', 'Account', 'Password'],
            collect($tutors)->map(function($t) {
                return [
                    $t->tutorID,
                    $t->first_name . ' ' . $t->last_name,
                    $t->username,
                    $t->email,
                    $t->account_name,
                    'tutor1234'
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('Note: All tutors use the default password: tutor1234');
    }
}
