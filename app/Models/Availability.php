<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $table = 'availabilities';
    protected $primaryKey = 'availID';
    
    protected $fillable = [
        'tutorID',
        'timeslotID',
        'availStatus'
    ];

    protected $casts = [
        'availStatus' => 'string',
    ];

    // Relationship back to tutor
    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutorID', 'tutorID');
    }
    
    // Relationship to time slot
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'timeslotID', 'timeslotID');
    }
    
    // Check if this availability slot is available
    public function isAvailable()
    {
        return $this->availStatus === 'available';
    }
}
