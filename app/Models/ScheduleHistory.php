<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleHistory extends Model
{
    use HasFactory;

    protected $table = 'schedule_history';

    protected $fillable = [
        'class_id',
        'class_name', 
        'school',
        'class_date',
        'class_time',
        'status',
        'action',
        'performed_by',
        'reason',
        'old_data',
        'new_data'
    ];

    protected $casts = [
        'class_date' => 'date',
        'class_time' => 'datetime:H:i:s',
        'old_data' => 'array',
        'new_data' => 'array'
    ];

    // Relationships
    public function dailyData()
    {
        return $this->belongsTo(DailyData::class, 'class_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('class_date', [$startDate, $endDate]);
    }

    public function scopeFromDate($query, $date)
    {
        return $query->where('class_date', '>=', $date);
    }

    public function scopeToDate($query, $date)
    {
        return $query->where('class_date', '<=', $date);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('performed_by', $userId);
    }
}
