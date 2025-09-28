<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tutor;

class UpdateAllGlsAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tutors that have GLS accounts
        $tutorsWithGls = Tutor::whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS');
        })->with(['accounts' => function($query) {
            $query->where('account_name', 'GLS');
        }])->get();

        $this->command->info("Found {$tutorsWithGls->count()} tutors with GLS accounts");

        $startingGlsId = 1000; // Start GLS IDs from 1000

        foreach ($tutorsWithGls as $index => $tutor) {
            $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
            
            if ($glsAccount) {
                // Generate unique GLS ID if not set
                $glsId = $glsAccount->gls_id ?: ($startingGlsId + $index);
                
                // Generate username and screen name based on tutor's first name
                $firstName = explode(' ', $tutor->full_name)[0];
                $username = $glsAccount->username ?: "OGS-{$firstName}";
                $screenName = $glsAccount->screen_name ?: "OGS-{$firstName}";
                
                // Update the GLS account
                DB::table('tutor_accounts')
                    ->where('id', $glsAccount->id)
                    ->update([
                        'gls_id' => $glsId,
                        'username' => $username,
                        'screen_name' => $screenName,
                        'updated_at' => now()
                    ]);

                $this->command->info("Updated GLS account for {$tutor->full_name}: GLS ID {$glsId}, Username: {$username}");
            }
        }

        $this->command->info('All GLS accounts updated successfully!');
        
        // Show final counts
        $totalGls = DB::table('tutor_accounts')->where('account_name', 'GLS')->count();
        $nullGlsIds = DB::table('tutor_accounts')->where('account_name', 'GLS')->whereNull('gls_id')->count();
        $nullUsernames = DB::table('tutor_accounts')->where('account_name', 'GLS')->whereNull('username')->count();
        
        $this->command->info("Final stats:");
        $this->command->info("- Total GLS accounts: {$totalGls}");
        $this->command->info("- Accounts with null gls_id: {$nullGlsIds}");
        $this->command->info("- Accounts with null username: {$nullUsernames}");
    }
}
