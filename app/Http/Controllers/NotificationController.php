<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated supervisor
     */
    public function index()
    {
        $supervisor = auth()->guard('supervisor')->user() ?? auth()->user();
        
        if (!$supervisor) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
        
        // Get supervisor ID
        $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;
        
        // Fetch notifications for supervisors only
        $notifications = Notification::where(function($query) use ($supervisorId) {
                $query->where('user_type', 'supervisor')
                      ->where('user_id', $supervisorId);
            })
            ->orWhereNull('user_type')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $unreadCount = Notification::where(function($query) use ($supervisorId) {
                $query->where('user_type', 'supervisor')
                      ->where('user_id', $supervisorId);
            })
            ->orWhereNull('user_type')
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Display all notifications page for the authenticated supervisor
     */
    public function viewAll()
    {
        $supervisor = auth()->guard('supervisor')->user() ?? auth()->user();
        
        if (!$supervisor) {
            return redirect()->route('login');
        }
        
        // Get supervisor ID
        $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;
        
        // Fetch notifications for supervisors only
        $notifications = Notification::where(function($query) use ($supervisorId) {
                $query->where('user_type', 'supervisor')
                      ->where('user_id', $supervisorId);
            })
            ->orWhereNull('user_type')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $unreadCount = Notification::where(function($query) use ($supervisorId) {
                $query->where('user_type', 'supervisor')
                      ->where('user_id', $supervisorId);
            })
            ->orWhereNull('user_type')
            ->where('is_read', false)
            ->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Create a new notification
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:info,success,warning,error',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|in:blue,green,yellow,red',
            'data' => 'nullable|array'
        ]);

        $notification = Notification::create([
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'icon' => $request->icon ?? $this->getDefaultIcon($request->type),
            'color' => $request->color ?? $this->getDefaultColor($request->type),
            'data' => $request->data
        ]);

        return response()->json($notification, 201);
    }

    /**
     * Get default icon based on type
     */
    private function getDefaultIcon($type)
    {
        return match($type) {
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
            'info' => 'fas fa-info-circle',
            default => 'fas fa-bell'
        };
    }

    /**
     * Get default color based on type
     */
    private function getDefaultColor($type)
    {
        return match($type) {
            'success' => 'green',
            'warning' => 'yellow',
            'error' => 'red',
            'info' => 'blue',
            default => 'blue'
        };
    }

    /**
     * Delete a notification
     */
    public function delete($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        
        return response()->json(['success' => true]);
    }
}
