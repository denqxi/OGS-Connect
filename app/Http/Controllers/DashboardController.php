<?php

namespace App\Http\Controllers;

use App\Models\ScheduleDailyData;
use App\Models\AssignedDailyData;
use App\Models\TutorAssignment;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\ScheduleHistory;
use App\Models\Application;
use App\Models\Demo;
use App\Models\Archive;
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
     * Get applicants this month (from applications table)
     */
    private function getApplicantsThisMonth($month)
    {
        return Application::whereYear('application_date_time', Carbon::parse($month . '-01')->year)
            ->whereMonth('application_date_time', Carbon::parse($month . '-01')->month)
            ->count();
    }

    /**
     * Get demo applicants (from Demo model - same data as for-demo.blade.php)
     */
    private function getDemoApplicants($month)
    {
        // Same logic as viewDemo method - exclude onboarding and hired applicants
        return Demo::whereNotIn('phase', ['onboarding', 'hired'])->count();
    }

    /**
     * Get onboarding applicants (from Demo model - same data as onboarding.blade.php)
     */
    private function getOnboardingApplicants($month)
    {
        // Same logic as viewOnboarding method - only show onboarding applicants
        return Demo::where('phase', 'onboarding')->count();
    }

    /**
     * Get existing employees count (from Tutor model - same data as employee management)
     */
    private function getExistingEmployees()
    {
        // Count all tutors that have active status
        return Tutor::where('status', 'active')->count();
    }

    /**
     * Get classes conducted (only finalized schedules - all time)
     */
    private function getClassesConducted($weekStart)
    {
        return AssignedDailyData::where('class_status', '!=', 'cancelled')
            ->whereNotNull('finalized_at')
            ->count();
    }

    /**
     * Get cancelled classes (only finalized schedules - all time)
     */
    private function getCancelledClasses($weekStart)
    {
        return AssignedDailyData::where('class_status', 'cancelled')
            ->whereNotNull('finalized_at')
            ->count();
    }

    /**
     * Get total classes (only finalized schedules - all time)
     */
    private function getTotalClasses($weekStart)
    {
        return AssignedDailyData::whereNotNull('finalized_at')
            ->count();
    }

    /**
     * Get fully assigned classes (only finalized schedules - all time)
     */
    private function getFullyAssignedClasses($weekStart)
    {
        return AssignedDailyData::whereNotNull('finalized_at')
            ->whereNotNull('main_tutor')
            ->count();
    }

    /**
     * Get partially assigned classes (only finalized schedules - all time)
     */
    private function getPartiallyAssignedClasses($weekStart)
    {
        return AssignedDailyData::whereNotNull('finalized_at')
            ->whereNull('main_tutor')
            ->whereNotNull('backup_tutor')
            ->count();
    }

    /**
     * Get unassigned classes (only finalized schedules - all time)
     */
    private function getUnassignedClasses($weekStart)
    {
        return AssignedDailyData::whereNotNull('finalized_at')
            ->whereNull('main_tutor')
            ->whereNull('backup_tutor')
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
            
            $conducted = ScheduleDailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->whereHas('assignedData', function($q) {
                    $q->where('class_status', '!=', 'cancelled')
                      ->whereNotNull('finalized_at');
                })
                ->count();
                
            $cancelled = ScheduleDailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->whereHas('assignedData', function($q) {
                    $q->where('class_status', 'cancelled')
                      ->whereNotNull('finalized_at');
                })
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
     * Get hiring statistics from archived applications (same data as archive.blade.php)
     */
    private function getHiringStats($month)
    {
        // Get hiring stats from Archive model - same data as archive.blade.php
        $notRecommended = Archive::where('status', 'not_recommended')->count();
        $noAnswer = Archive::where('status', 'no_answer_3_attempts')->count();
        $declined = Archive::where('status', 'declined')->count();
        
        return [
            'not_recommended' => $notRecommended,
            'no_answer' => $noAnswer,
            'declined' => $declined
        ];
    }

    /**
     * Get active tutors count
     */
    private function getActiveTutorsCount()
    {
        return Tutor::where('status', 'active')
            ->whereHas('account', function($query) {
                $query->where('account_name', 'GLS');
            })->count();
    }

    /**
     * Get tutor utilization rate (only finalized schedules - all time)
     */
    private function getTutorUtilization($weekStart)
    {
        $totalTutors = $this->getActiveTutorsCount();
        // Count unique tutors assigned to finalized schedules
        $assignedTutors = AssignedDailyData::whereNotNull('finalized_at')
            ->where(function($q) {
                $q->whereNotNull('main_tutor')
                  ->orWhereNotNull('backup_tutor');
            })
            ->distinct()
            ->count(DB::raw('COALESCE(main_tutor, backup_tutor)'));
        
        return $totalTutors > 0 ? round(($assignedTutors / $totalTutors) * 100, 1) : 0;
    }

    /**
     * Get schedule status breakdown (all time)
     */
    private function getScheduleStatusBreakdown($weekStart)
    {
        $finalized = AssignedDailyData::whereNotNull('finalized_at')->count();
        $notFinalized = ScheduleDailyData::whereDoesntHave('assignedData', function($q) {
            $q->whereNotNull('finalized_at');
        })->count();
            
        return [
            'finalized' => $finalized,
            'tentative' => $notFinalized,
            'draft' => 0,
            'null' => 0
        ];
    }

    /**
     * Get recent activity from schedule history
     */
    private function getRecentActivity()
    {
        return ScheduleHistory::with('dailyData')
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
