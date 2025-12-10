<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user (supervisor or tutor)
     */
    public function index()
    {
        // Check both guards
        $supervisor = auth()->guard('supervisor')->user();
        $tutor = auth()->guard('tutor')->user();
        
        // Determine which user is authenticated
        if ($supervisor) {
            $userId = $supervisor->supervisor_id ?? $supervisor->id;
            $userType = 'supervisor';
        } elseif ($tutor) {
            $userId = $tutor->tutor_id ?? $tutor->id;
            $userType = 'tutor';
        } else {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
        
        // Fetch notifications for the specific user and user type
        $notifications = Notification::where('user_id', $userId)
            ->where('user_type', $userType)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $unreadCount = Notification::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Display all notifications page for the authenticated user
     */
    public function viewAll()
    {
        // Check both guards
        $supervisor = auth()->guard('supervisor')->user();
        $tutor = auth()->guard('tutor')->user();
        
        // Determine which user is authenticated
        if ($supervisor) {
            $userId = $supervisor->supervisor_id ?? $supervisor->id;
            $userType = 'supervisor';
        } elseif ($tutor) {
            $userId = $tutor->tutor_id ?? $tutor->id;
            $userType = 'tutor';
        } else {
            return redirect()->route('login');
        }
        
        // Fetch notifications for the specific user and user type
        $notifications = Notification::where('user_id', $userId)
            ->where('user_type', $userType)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $unreadCount = Notification::where('user_id', $userId)
            ->where('user_type', $userType)
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
        // Get the authenticated user (supervisor or tutor)
        $user = auth()->guard('supervisor')->user() ?? auth()->guard('tutor')->user() ?? auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Determine user type and ID
        if (auth()->guard('supervisor')->check()) {
            $userId = $user->supervisor_id ?? $user->id;
            $userType = 'supervisor';
        } else if (auth()->guard('tutor')->check()) {
            $userId = $user->tutor_id ?? $user->id;
            $userType = 'tutor';
        } else {
            // Fallback for default auth
            $userId = $user->id;
            $userType = 'supervisor'; // or determine based on user model
        }
        
        // Mark only the current user's notifications as read
        Notification::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('is_read', false)
            ->update([
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
