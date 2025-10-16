<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'user_type',
        'user_id',
        'user_email',
        'user_name',
        'action',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'severity',
        'is_important'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_important' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope for important logs
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope for high severity events
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by event type
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Get badge color for severity level
     */
    public function getSeverityBadgeColorAttribute()
    {
        return match($this->severity) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get event type icon
     */
    public function getEventTypeIconAttribute()
    {
        return match($this->event_type) {
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'password_reset' => 'fas fa-key',
            'schedule_change' => 'fas fa-calendar-edit',
            'tutor_assignment' => 'fas fa-user-plus',
            'tutor_removal' => 'fas fa-user-minus',
            'tutor_activated' => 'fas fa-user-check',
            'tutor_deactivated' => 'fas fa-user-times',
            'tutor_restored' => 'fas fa-user-plus',
            'tutors_bulk_restored' => 'fas fa-users',
            'supervisor_activated' => 'fas fa-user-tie',
            'supervisor_deactivated' => 'fas fa-user-slash',
            'supervisor_restored' => 'fas fa-user-tie',
            'supervisors_bulk_restored' => 'fas fa-users-cog',
            'class_cancellation' => 'fas fa-ban',
            'data_export' => 'fas fa-download',
            'security_violation' => 'fas fa-exclamation-triangle',
            'system_error' => 'fas fa-bug',
            default => 'fas fa-info-circle'
        };
    }

    /**
     * Get formatted timestamp with timezone
     */
    public function getFormattedTimestampAttribute()
    {
        return $this->created_at->format('F j, Y \a\t H:i:s T');
    }

    /**
     * Get formatted short timestamp with timezone  
     */
    public function getFormattedShortTimestampAttribute()
    {
        return $this->created_at->format('M j, Y H:i:s T');
    }

    /**
     * Static method to log events
     */
    public static function logEvent(
        $eventType, 
        $userType, 
        $userId, 
        $userEmail, 
        $userName, 
        $action, 
        $description, 
        $metadata = null, 
        $severity = 'low', 
        $isImportant = false, 
        $request = null
    ) {
        return self::create([
            'event_type' => $eventType,
            'user_type' => $userType,
            'user_id' => $userId,
            'user_email' => $userEmail,
            'user_name' => $userName,
            'action' => $action,
            'description' => $description,
            'metadata' => null, // Removed for simplicity
            'ip_address' => null, // Removed for privacy
            'user_agent' => null, // Removed for privacy
            'severity' => $severity,
            'is_important' => $isImportant
        ]);
    }
}
