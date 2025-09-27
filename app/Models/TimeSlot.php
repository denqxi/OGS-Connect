<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeSlot extends Model
{
    protected $table = 'time_slots';
    protected $primaryKey = 'timeslotID';
    
    protected $fillable = [
        'date',
        'startTime',
        'endTime'
    ];

    protected $casts = [
        'date' => 'date',
        'startTime' => 'datetime:H:i:s',
        'endTime' => 'datetime:H:i:s'
    ];

    // Relationship to availabilities
    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'timeslotID', 'timeslotID');
    }
    
    // Helper to get day name from date
    public function getDayAttribute()
    {
        return Carbon::parse($this->date)->format('l'); // Full day name (e.g., Monday)
    }
    
    // Helper to get formatted time range
    public function getFormattedTimeAttribute()
    {
        $start = Carbon::parse($this->startTime);
        $end = Carbon::parse($this->endTime);
        return $start->format('g A') . ' - ' . $end->format('g A');
    }
}