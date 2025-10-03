<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supervisor;

class UpdateExistingSupervisors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervisors:update-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing supervisors with assigned accounts and missing fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating existing supervisors...');
        
        // Get all supervisors
        $supervisors = Supervisor::all();
        
        if ($supervisors->isEmpty()) {
            $this->info('No supervisors found.');
            return;
        }
        
        $this->info("Found {$supervisors->count()} supervisors:");
        
        foreach ($supervisors as $supervisor) {
            $this->line("- {$supervisor->supID}: {$supervisor->sfname} {$supervisor->slname} (Account: " . ($supervisor->assigned_account ?? 'None') . ")");
        }
        
        // Ask which account to assign to existing supervisors
        $account = $this->choice(
            'Which account should existing supervisors be assigned to?',
            ['GLS', 'Tutlo', 'Babilala', 'Talk915', 'Skip'],
            'GLS'
        );
        
        if ($account === 'Skip') {
            $this->info('Skipping account assignment.');
            return;
        }
        
        // Update supervisors without assigned accounts
        $updated = 0;
        foreach ($supervisors as $supervisor) {
            if (!$supervisor->assigned_account) {
                $supervisor->update([
                    'assigned_account' => $account,
                    'srole' => $account . ' Supervisor',
                    'status' => 'active',
                    'susername' => strtolower($supervisor->sfname . '.' . $supervisor->slname)
                ]);
                $updated++;
                $this->line("✓ Updated {$supervisor->supID}: {$supervisor->sfname} {$supervisor->slname} -> {$account} Supervisor");
            } else {
                $this->line("- Skipped {$supervisor->supID}: {$supervisor->sfname} {$supervisor->slname} (already has account: {$supervisor->assigned_account})");
            }
        }
        
        $this->info("Updated {$updated} supervisors with {$account} account assignment.");
    }
}