<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyData;
use Carbon\Carbon;

class FixDayValues extends Command
{
    protected $signature = 'schedule:fix-days';
    protected $description = 'Fix day values in daily_data table by recalculating from date';

    public function handle()
    {
        $this->info('Starting to fix day values...');
        
        $records = DailyData::all();
        $this->info("Found {$records->count()} records");
        $fixed = 0;
        
        foreach ($records as $record) {
            if ($record->date) {
                $correctDay = Carbon::parse($record->date)->format('l');
            $this->line("Checking ID {$record->id}: date={$record->date}, day='{$record->day}', calculated={$correctDay}");
                
                if (empty($record->day) || $record->day !== $correctDay) {
                    $this->line("Fixing ID {$record->id}: Date={$record->date}, Wrong={$record->day} -> Correct={$correctDay}");
                    $record->day = $correctDay;
                    $record->save();
                    $fixed++;
                }
            }
        }
        
        $this->info("Fixed {$fixed} records!");
        return 0;
    }
}
