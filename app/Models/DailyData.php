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
        'schedule_status',
        'finalized_at',
        'finalized_by',
        'class_status', // Added class cancellation status
        'cancelled_at'  // Added cancellation timestamp
    ];

    protected $casts = [
        'date' => 'date',
        'time_jst' => 'datetime:H:i:s',
        'time_pht' => 'datetime:H:i:s',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime' // Added cancelled_at casting
    ];

    // ADD THIS RELATIONSHIP
    public function tutorAssignments()
    {
        return $this->hasMany(TutorAssignment::class);
    }

    // History relationship
    public function scheduleHistory()
    {
        return $this->hasMany(ScheduleHistory::class, 'class_id');
    }

    // Finalized by user relationship
    public function finalizedBy()
    {
        return $this->belongsTo(User::class, 'finalized_by');
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

    // Schedule status helpers
    public function isFinalized()
    {
        return $this->schedule_status === 'finalized';
    }

    public function isDraft()
    {
        return $this->schedule_status === 'draft';
    }

    public function isTentative()
    {
        return $this->schedule_status === 'tentative';
    }

    public function canBeEdited()
    {
        // Only draft and tentative schedules can be fully edited
        // Finalized schedules can only be cancelled or rescheduled
        return in_array($this->schedule_status, ['draft', 'tentative']) && $this->class_status !== 'cancelled';
    }

    // Create history record
    public function createHistoryRecord($action, $performedBy = null, $reason = null, $oldData = null, $newData = null)
    {
        $status = $this->schedule_status === 'final' ? 'finalized' : $this->schedule_status;
        return ScheduleHistory::create([
            'class_id' => $this->id,
            'class_name' => $this->class,
            'school' => $this->school,
            'class_date' => $this->date,
            'class_time' => $this->time_jst,
            'status' => $status,
            'action' => $action,
            'performed_by' => $performedBy,
            'reason' => $reason,
            'old_data' => $oldData,
            'new_data' => $newData
        ]);
    }
}
