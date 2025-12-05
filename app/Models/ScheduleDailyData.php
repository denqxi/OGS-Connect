<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDailyData extends Model
{
    use HasFactory;

    protected $table = 'schedules_daily_data';

    protected $fillable = [
        'date',
        'day',
        'time',
        'duration',
        'school',
        'class',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
    ];

    /**
     * Get the assignment details for this schedule
     */
    public function assignedData()
    {
        return $this->hasOne(AssignedDailyData::class, 'schedule_daily_data_id');
    }

    /**
     * Get tutor assignments (legacy compatibility)
     */
    public function tutorAssignments()
    {
        return $this->hasMany(TutorAssignment::class, 'daily_data_id');
    }

    /**
     * Get schedule history
     */
    public function scheduleHistory()
    {
        return $this->hasMany(ScheduleHistory::class, 'class_id');
    }
    
    /**
     * Accessor: Get main tutor from assigned data
     */
    public function getMainTutorAttribute()
    {
        return $this->assignedData?->mainTutor;
    }

    /**
     * Accessor: Get backup tutor from assigned data
     */
    public function getBackupTutorAttribute()
    {
        return $this->assignedData?->backupTutor;
    }

    /**
     * Accessor: Get assigned supervisor from assigned data
     */
    public function getAssignedSupervisorAttribute()
    {
        return $this->assignedData?->assigned_supervisor;
    }

    /**
     * Accessor: Get finalization status
     */
    public function getIsFinalizedAttribute()
    {
        return !is_null($this->assignedData?->finalized_at);
    }

    /**
     * Accessor: Get assignment status
     */
    public function getAssignmentStatusAttribute()
    {
        if (!$this->assignedData) {
            return 'unassigned';
        }

        $hasMain = !is_null($this->assignedData->main_tutor);
        $hasBackup = !is_null($this->assignedData->backup_tutor);

        if ($hasMain && $hasBackup) {
            return 'fully_assigned';
        } elseif ($hasMain) {
            return 'partial';
        }

        return 'unassigned';
    }

    /**
     * Scope: Filter by date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope: Filter by school
     */
    public function scopeForSchool($query, $school)
    {
        return $query->where('school', $school);
    }

    /**
     * Scope: Active classes only
     */
    public function scopeActive($query)
    {
        return $query->whereHas('assignedData', function($q) {
            $q->where('class_status', 'active');
        });
    }

    /**
     * Scope: Cancelled classes only
     */
    public function scopeCancelled($query)
    {
        return $query->whereHas('assignedData', function($q) {
            $q->where('class_status', 'cancelled');
        });
    }
}
