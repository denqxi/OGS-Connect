<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    /**
     * Display audit logs with filtering
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('important_only')) {
            $query->where('is_important', true);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get filter options
        $eventTypes = AuditLog::distinct()->pluck('event_type')->filter()->sort();
        $userTypes = AuditLog::distinct()->pluck('user_type')->filter()->sort();
        $severities = ['low', 'medium', 'high', 'critical'];

        // Get summary statistics
        $stats = [
            'total_logs' => AuditLog::count(),
            'important_logs' => AuditLog::where('is_important', true)->count(),
            'high_severity_logs' => AuditLog::whereIn('severity', ['high', 'critical'])->count(),
            'recent_logs' => AuditLog::where('created_at', '>=', now()->subDays(7))->count(),
            'login_attempts' => AuditLog::where('event_type', 'login')->where('created_at', '>=', now()->subDays(7))->count(),
            'failed_logins' => AuditLog::where('event_type', 'login_failed')->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('audit.index', compact('logs', 'eventTypes', 'userTypes', 'severities', 'stats'));
    }

    /**
     * Show detailed audit log
     */
    public function show(AuditLog $auditLog)
    {
        return view('audit.show', compact('auditLog'));
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('important_only')) {
            $query->where('is_important', true);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $logs = $query->limit(10000)->get(); // Limit for performance

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date/Time',
                'Event Type',
                'User Type',
                'User Email',
                'User Name',
                'Action',
                'Description',
                'Severity',
                'Important',
                'IP Address'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->event_type,
                    $log->user_type,
                    $log->user_email,
                    $log->user_name,
                    $log->action,
                    $log->description,
                    $log->severity,
                    $log->is_important ? 'Yes' : 'No',
                    $log->ip_address
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit log statistics
     */
    public function getStats()
    {
        $stats = [
            'today' => [
                'total' => AuditLog::whereDate('created_at', today())->count(),
                'important' => AuditLog::whereDate('created_at', today())->where('is_important', true)->count(),
                'logins' => AuditLog::whereDate('created_at', today())->where('event_type', 'login')->count(),
            ],
            'week' => [
                'total' => AuditLog::where('created_at', '>=', now()->subDays(7))->count(),
                'important' => AuditLog::where('created_at', '>=', now()->subDays(7))->where('is_important', true)->count(),
                'logins' => AuditLog::where('created_at', '>=', now()->subDays(7))->where('event_type', 'login')->count(),
            ],
            'month' => [
                'total' => AuditLog::where('created_at', '>=', now()->subDays(30))->count(),
                'important' => AuditLog::where('created_at', '>=', now()->subDays(30))->where('is_important', true)->count(),
                'logins' => AuditLog::where('created_at', '>=', now()->subDays(30))->where('event_type', 'login')->count(),
            ]
        ];

        return response()->json($stats);
    }
}
