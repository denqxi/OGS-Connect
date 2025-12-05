<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDayValues extends Command
{
    protected $signature = 'schedule:check-days';
    protected $description = 'Check day values in daily_data table';

    public function handle()
    {
        $records = DB::table('daily_data')
            ->select('id', 'date', 'day', 'school')
            ->limit(20)
            ->get();
        
        $this->info('Checking day values in database:');
        $this->line('');
        
        foreach ($records as $record) {
            $calculatedDay = \Carbon\Carbon::parse($record->date)->format('l');
            $match = $record->day === $calculatedDay ? '✓' : '✗';
            
            $this->line("{$match} ID: {$record->id} | Date: {$record->date} | Stored Day: {$record->day} | Calculated: {$calculatedDay} | School: {$record->school}");
        }
        
        return 0;
    }
}
