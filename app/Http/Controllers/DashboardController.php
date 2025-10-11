<?php

namespace App\Http\Controllers;

use App\Models\DailyData;
use App\Models\TutorAssignment;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\ScheduleHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real data
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        
        return view('dashboard.dashboard', compact('stats'));
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStats()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $currentWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        
        return [
            // Top 4 Stat Boxes
            'applicants_this_month' => $this->getApplicantsThisMonth($currentMonth),
            'demo_applicants' => $this->getDemoApplicants($currentMonth),
            'onboarding_applicants' => $this->getOnboardingApplicants($currentMonth),
            'existing_employees' => $this->getExistingEmployees(),
            
            // GLS Scheduling Reports
            'classes_conducted' => $this->getClassesConducted($currentWeek),
            'cancelled_classes' => $this->getCancelledClasses($currentWeek),
            'total_classes' => $this->getTotalClasses($currentWeek),
            'fully_assigned_classes' => $this->getFullyAssignedClasses($currentWeek),
            'partially_assigned_classes' => $this->getPartiallyAssignedClasses($currentWeek),
            'unassigned_classes' => $this->getUnassignedClasses($currentWeek),
            
            // Weekly trends
            'weekly_trends' => $this->getWeeklyTrends(),
            
            // Hiring & Onboarding Reports
            'hiring_stats' => $this->getHiringStats($currentMonth),
            
            // Tutor statistics
            'active_tutors' => $this->getActiveTutorsCount(),
            'tutor_utilization' => $this->getTutorUtilization($currentWeek),
            
            // Schedule status breakdown
            'schedule_status_breakdown' => $this->getScheduleStatusBreakdown($currentWeek),
            
            // Recent activity
            'recent_activity' => $this->getRecentActivity()
        ];
    }

    /**
     * Get applicants this month (placeholder - would need application data)
     */
    private function getApplicantsThisMonth($month)
    {
        // This would typically come from an applications table
        // For now, return a calculated estimate based on tutor accounts
        $newTutorsThisMonth = Tutor::whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        })
        ->where('created_at', '>=', Carbon::parse($month . '-01'))
        ->count();
        
        return $newTutorsThisMonth * 3; // Estimate multiplier
    }

    /**
     * Get demo applicants (placeholder)
     */
    private function getDemoApplicants($month)
    {
        // This would come from application status data
        return 45; // Placeholder
    }

    /**
     * Get onboarding applicants (placeholder)
     */
    private function getOnboardingApplicants($month)
    {
        // This would come from application status data
        return 32; // Placeholder
    }

    /**
     * Get existing employees count
     */
    private function getExistingEmployees()
    {
        return Tutor::whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        })->count();
    }

    /**
     * Get classes conducted (only finalized schedules - all time)
     */
    private function getClassesConducted($weekStart)
    {
        return DailyData::where('class_status', '!=', 'cancelled')
            ->where('schedule_status', 'finalized')
            ->count();
    }

    /**
     * Get cancelled classes (only finalized schedules - all time)
     */
    private function getCancelledClasses($weekStart)
    {
        return DailyData::where('class_status', 'cancelled')
            ->where('schedule_status', 'finalized')
            ->count();
    }

    /**
     * Get total classes (only finalized schedules - all time)
     */
    private function getTotalClasses($weekStart)
    {
        return DailyData::where('schedule_status', 'finalized')
            ->count();
    }

    /**
     * Get fully assigned classes (only finalized schedules - all time)
     */
    private function getFullyAssignedClasses($weekStart)
    {
        return DailyData::where('schedule_status', 'finalized')
            ->withCount('tutorAssignments')
            ->get()
            ->filter(function($class) {
                return $class->tutor_assignments_count >= $class->number_required;
            })
            ->count();
    }

    /**
     * Get partially assigned classes (only finalized schedules - all time)
     */
    private function getPartiallyAssignedClasses($weekStart)
    {
        return DailyData::where('schedule_status', 'finalized')
            ->withCount('tutorAssignments')
            ->get()
            ->filter(function($class) {
                $assigned = $class->tutor_assignments_count;
                $required = $class->number_required;
                return $assigned > 0 && $assigned < $required;
            })
            ->count();
    }

    /**
     * Get unassigned classes (only finalized schedules - all time)
     */
    private function getUnassignedClasses($weekStart)
    {
        return DailyData::where('schedule_status', 'finalized')
            ->withCount('tutorAssignments')
            ->get()
            ->filter(function($class) {
                return $class->tutor_assignments_count === 0;
            })
            ->count();
    }

    /**
     * Get weekly trends for the last 4 weeks (only finalized schedules)
     */
    private function getWeeklyTrends()
    {
        $weeks = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            $conducted = DailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->where('class_status', '!=', 'cancelled')
                ->where('schedule_status', 'finalized')
                ->count();
                
            $cancelled = DailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->where('class_status', 'cancelled')
                ->where('schedule_status', 'finalized')
                ->count();
            
            $weeks[] = [
                'week' => 'Week ' . (4 - $i),
                'date_range' => $weekStart->format('M j') . ' – ' . $weekEnd->format('M j'),
                'conducted' => $conducted,
                'cancelled' => $cancelled
            ];
        }
        
        return $weeks;
    }

    /**
     * Get hiring statistics (placeholder)
     */
    private function getHiringStats($month)
    {
        // This would come from application status data
        return [
            'not_recommended' => 15,
            'no_answer' => 10,
            'declined' => 7
        ];
    }

    /**
     * Get active tutors count
     */
    private function getActiveTutorsCount()
    {
        return Tutor::whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        })->count();
    }

    /**
     * Get tutor utilization rate (only finalized schedules - all time)
     */
    private function getTutorUtilization($weekStart)
    {
        $totalTutors = $this->getActiveTutorsCount();
        $assignedTutors = TutorAssignment::whereHas('dailyData', function($query) {
            $query->where('schedule_status', 'finalized');
        })->distinct('tutor_id')->count();
        
        return $totalTutors > 0 ? round(($assignedTutors / $totalTutors) * 100, 1) : 0;
    }

    /**
     * Get schedule status breakdown (all time)
     */
    private function getScheduleStatusBreakdown($weekStart)
    {
        $statuses = DailyData::select('schedule_status', DB::raw('count(*) as count'))
            ->groupBy('schedule_status')
            ->get()
            ->pluck('count', 'schedule_status')
            ->toArray();
            
        return [
            'finalized' => $statuses['finalized'] ?? 0,
            'tentative' => $statuses['tentative'] ?? 0,
            'draft' => $statuses['draft'] ?? 0,
            'null' => $statuses[null] ?? 0
        ];
    }

    /**
     * Get recent activity from schedule history
     */
    private function getRecentActivity()
    {
        return ScheduleHistory::with('dailyData')
            ->where('created_at', '>=', now()->subHours(24)) // Only show activities from last 24 hours
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($activity) {
                return [
                    'action' => $activity->action,
                    'class_name' => $activity->class_name,
                    'school' => $activity->school,
                    'date' => $activity->class_date,
                    'time' => $activity->class_time,
                    'performed_at' => $activity->created_at,
                    'reason' => $activity->reason
                ];
            });
    }

    /**
     * Clear old schedule history records (older than 1 day)
     */
    public function clearOldHistory()
    {
        try {
            $deletedCount = ScheduleHistory::where('created_at', '<', now()->subDay())->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Cleared {$deletedCount} old schedule history records",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear old history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint for dashboard data
     */
    public function getDashboardData()
    {
        return response()->json($this->getDashboardStats());
    }

    /**
     * API endpoint for weekly trends
     */
    public function getWeeklyTrendsData()
    {
        return response()->json([
            'trends' => $this->getWeeklyTrends(),
            'current_week' => $this->getCurrentWeekStats()
        ]);
    }

    /**
     * Get current week statistics
     */
    private function getCurrentWeekStats()
    {
        $weekStart = Carbon::now()->startOfWeek();
        
        return [
            'classes_conducted' => $this->getClassesConducted($weekStart),
            'cancelled_classes' => $this->getCancelledClasses($weekStart),
            'total_classes' => $this->getTotalClasses($weekStart),
            'fully_assigned' => $this->getFullyAssignedClasses($weekStart),
            'partially_assigned' => $this->getPartiallyAssignedClasses($weekStart),
            'unassigned' => $this->getUnassignedClasses($weekStart),
            'tutor_utilization' => $this->getTutorUtilization($weekStart)
        ];
    }
}
