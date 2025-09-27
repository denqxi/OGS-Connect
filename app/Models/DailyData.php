<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyData extends Model
{
    use HasFactory;

    protected $table = 'daily_data';

    protected $fillable = [
        'date',
        'day', 
        'school',
        'class',
        'duration',
        'time_jst',
        'time_pht',
        'number_required',
    ];

    protected $casts = [
        'date' => 'date',
        'time_jst' => 'datetime:H:i:s',
        'time_pht' => 'datetime:H:i:s'
    ];

    // ADD THIS RELATIONSHIP
    public function tutorAssignments()
    {
        return $this->hasMany(TutorAssignment::class);
    }

    // Optional: Get assignment status
    public function getAssignmentStatusAttribute()
    {
        $assignedCount = $this->tutorAssignments()->count();
        $requiredCount = $this->number_required;

        if ($assignedCount === 0) {
            return 'unassigned';
        } elseif ($assignedCount < $requiredCount) {
            return 'partial';
        } else {
            return 'assigned';
        }
    }

    // Optional: Get status display text
    public function getStatusDisplayAttribute()
    {
        switch ($this->assignment_status) {
            case 'unassigned':
                return 'Unassigned';
            case 'partial':
                return 'Partially Assigned';
            case 'assigned':
                return 'Fully Assigned';
            default:
                return 'Unknown';
        }
    }

    // Optional: Get status color class
    public function getStatusColorAttribute()
    {
        switch ($this->assignment_status) {
            case 'unassigned':
                return 'bg-red-100 text-red-800';
            case 'partial':
                return 'bg-yellow-100 text-yellow-800';
            case 'assigned':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}
