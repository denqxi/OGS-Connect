<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'is_read',
        'read_at',
        'data'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array'
    ];

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Get color classes for the notification
     */
    public function getColorClasses()
    {
        return match($this->color) {
            'green' => 'bg-green-50 border-green-200 text-green-800',
            'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'red' => 'bg-red-50 border-red-200 text-red-800',
            'blue' => 'bg-blue-50 border-blue-200 text-blue-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800'
        };
    }

    /**
     * Get icon color classes
     */
    public function getIconColorClasses()
    {
        return match($this->color) {
            'green' => 'text-green-500',
            'yellow' => 'text-yellow-500',
            'red' => 'text-red-500',
            'blue' => 'text-blue-500',
            default => 'text-blue-500'
        };
    }
}
