<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedDailyData extends Model
{
    use HasFactory;

    protected $table = 'assigned_daily_data';

    protected $fillable = [
        'schedule_daily_data_id',
        'class_status',
        'main_tutor',
        'backup_tutor',
        'assigned_supervisor',
        'finalized_at',
        'finalized_by',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the schedule this assignment belongs to
     */
    public function schedule()
    {
        return $this->belongsTo(ScheduleDailyData::class, 'schedule_daily_data_id');
    }

    /**
     * Get the main tutor
     */
    public function mainTutor()
    {
        return $this->belongsTo(Tutor::class, 'main_tutor', 'tutor_id');
    }

    /**
     * Get the backup tutor
     */
    public function backupTutor()
    {
        return $this->belongsTo(Tutor::class, 'backup_tutor', 'tutor_id');
    }

    /**
     * Get the supervisor who finalized this assignment
     */
    public function finalizedBySupervisor()
    {
        return $this->belongsTo(Supervisor::class, 'finalized_by', 'supervisor_id');
    }

    /**
     * Get the assigned supervisor (by supID string)
     */
    public function assignedSupervisorModel()
    {
        return $this->belongsTo(Supervisor::class, 'assigned_supervisor', 'supID');
    }

    /**
     * Get the assigned supervisor (alias for easier access)
     */
    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'assigned_supervisor', 'supervisor_id');
    }

    /**
     * Get the work detail submitted by the tutor for this assignment
     */
    public function workDetail()
    {
        return $this->hasOne(TutorWorkDetail::class, 'assignment_id', 'id');
    }

    /**
     * Scope: Active assignments only
     */
    public function scopeActive($query)
    {
        return $query->where('class_status', 'active');
    }

    /**
     * Scope: Cancelled assignments only
     */
    public function scopeCancelled($query)
    {
        return $query->where('class_status', 'cancelled');
    }

    /**
     * Scope: Finalized assignments only
     */
    public function scopeFinalized($query)
    {
        return $query->whereNotNull('finalized_at');
    }

    /**
     * Check if assignment is finalized
     */
    public function isFinalized()
    {
        return !is_null($this->finalized_at);
    }

    /**
     * Check if assignment is cancelled
     */
    public function isCancelled()
    {
        return $this->class_status === 'cancelled';
    }

    /**
     * Mark as finalized
     */
    public function finalize($supervisorId)
    {
        $this->update([
            'finalized_at' => now(),
            'finalized_by' => $supervisorId,
        ]);
    }

    /**
     * Cancel the assignment
     */
    public function cancel($reason = null)
    {
        $this->update([
            'class_status' => 'cancelled',
            'cancelled_at' => now(),
            'notes' => $reason,
        ]);
    }
}
