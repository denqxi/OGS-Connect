<?php

namespace App\Http\Controllers;

use App\Models\ScheduleDailyData;
use App\Models\AssignedDailyData;
use App\Models\TutorAssignment;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\ScheduleHistory;
use App\Models\Application;
use App\Models\Applicant;
use App\Models\Demo;
use App\Models\Archive;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real data
     */
    public function index(Request $request)
    {
        $stats = $this->getDashboardStats($request);
        
        return view('dashboard.dashboard', compact('stats'));
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStats(Request $request = null)
    {
        // Get filter parameters
        $month = $request?->input('month') ?? Carbon::now()->format('Y-m');
        $fromDate = $request?->input('from_date');
        $toDate = $request?->input('to_date');
        $account = $request?->input('account');
        
        // Determine date range
        if ($fromDate && $toDate) {
            $currentMonth = null; // Will use date range instead
            $currentWeek = Carbon::parse($fromDate);
        } else {
            $currentMonth = $month;
            $currentWeek = Carbon::now()->startOfWeek();
        }
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        
        return [
            // Top 4 Stat Boxes
            'applicants_this_month' => $this->getApplicantsThisMonth($currentMonth, $fromDate, $toDate, $account),
            'demo_applicants' => $this->getDemoApplicants($currentMonth, $fromDate, $toDate, $account),
            'onboarding_applicants' => $this->getOnboardingApplicants($currentMonth, $fromDate, $toDate, $account),
            'existing_employees' => $this->getExistingEmployees($account),
            
            // GLS Scheduling Reports
            'classes_conducted' => $this->getClassesConducted($currentWeek, $fromDate, $toDate, $account),
            'cancelled_classes' => $this->getCancelledClasses($currentWeek, $fromDate, $toDate, $account),
            'total_classes' => $this->getTotalClasses($currentWeek, $fromDate, $toDate, $account),
            'fully_assigned_classes' => $this->getFullyAssignedClasses($currentWeek, $fromDate, $toDate, $account),
            'partially_assigned_classes' => $this->getPartiallyAssignedClasses($currentWeek, $fromDate, $toDate, $account),
            'unassigned_classes' => $this->getUnassignedClasses($currentWeek, $fromDate, $toDate, $account),
            
            // Weekly trends
            'weekly_trends' => $this->getWeeklyTrends($fromDate, $toDate, $account),
            
            // Hiring & Onboarding Reports
            'hiring_stats' => $this->getHiringStats($currentMonth, $fromDate, $toDate, $account),
            
            // Tutor statistics
            'active_tutors' => $this->getActiveTutorsCount($account),
            'tutor_utilization' => $this->getTutorUtilization($currentWeek, $fromDate, $toDate, $account),
            
            // Schedule status breakdown
            'schedule_status_breakdown' => $this->getScheduleStatusBreakdown($currentWeek, $fromDate, $toDate, $account),
            
            // Recent activity
            'recent_activity' => $this->getRecentActivity()
        ];
    }

    /**
     * Get applicants this month (from applications table)
     */
    private function getApplicantsThisMonth($month = null, $fromDate = null, $toDate = null, $account = null)
    {
        $query = Application::query();
        
        if ($fromDate && $toDate) {
            $query->whereBetween('application_date_time', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        } elseif ($month) {
            $query->whereYear('application_date_time', Carbon::parse($month . '-01')->year)
                  ->whereMonth('application_date_time', Carbon::parse($month . '-01')->month);
        }
        
        // Note: applications table doesn't have account field, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get demo applicants (from Demo model - same data as for-demo.blade.php)
     */
    private function getDemoApplicants($month = null, $fromDate = null, $toDate = null, $account = null)
    {
        $query = Demo::whereNotIn('phase', ['onboarding', 'hired']);
        
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        if ($account) {
            // Get account ID from account name
            $accountId = Account::where('account_name', $account)->first()?->account_id;
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
        }
        
        return $query->count();
    }

    /**
     * Get onboarding applicants (from Demo model - same data as onboarding.blade.php)
     */
    private function getOnboardingApplicants($month = null, $fromDate = null, $toDate = null, $account = null)
    {
        $query = Demo::where('phase', 'onboarding');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        if ($account) {
            // Get account ID from account name
            $accountId = Account::where('account_name', $account)->first()?->account_id;
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
        }
        
        return $query->count();
    }

    /**
     * Get existing employees count (from Tutor model - same data as employee management)
     */
    private function getExistingEmployees($account = null)
    {
        $query = Tutor::where('status', 'active');
        
        if ($account) {
            // Get account ID from account name
            $accountId = Account::where('account_name', $account)->first()?->account_id;
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
        }
        
        return $query->count();
    }

    /**
     * Get classes conducted (only finalized schedules - all time)
     */
    private function getClassesConducted($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::where('class_status', '!=', 'cancelled')
            ->whereNotNull('finalized_at');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get cancelled classes (only finalized schedules - all time)
     */
    private function getCancelledClasses($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::where('class_status', 'cancelled')
            ->whereNotNull('finalized_at');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get total classes (only finalized schedules - all time)
     */
    private function getTotalClasses($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::whereNotNull('finalized_at');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get fully assigned classes (only finalized schedules - all time)
     */
    private function getFullyAssignedClasses($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::whereNotNull('finalized_at')
            ->whereNotNull('main_tutor');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get partially assigned classes (only finalized schedules - all time)
     */
    private function getPartiallyAssignedClasses($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::whereNotNull('finalized_at')
            ->whereNull('main_tutor')
            ->whereNotNull('backup_tutor');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get unassigned classes (only finalized schedules - all time)
     */
    private function getUnassignedClasses($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::whereNotNull('finalized_at')
            ->whereNull('main_tutor')
            ->whereNull('backup_tutor');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        return $query->count();
    }

    /**
     * Get weekly trends for the last 4 weeks (only finalized schedules)
     */
    private function getWeeklyTrends($fromDate = null, $toDate = null, $account = null)
    {
        $weeks = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            $conductedQuery = ScheduleDailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->whereHas('assignedData', function($q) {
                    $q->where('class_status', '!=', 'cancelled')
                      ->whereNotNull('finalized_at');
                });
            
            $conducted = $conductedQuery->count();
            
            $cancelledQuery = ScheduleDailyData::whereBetween('date', [$weekStart, $weekEnd])
                ->whereHas('assignedData', function($q) {
                    $q->where('class_status', 'cancelled')
                      ->whereNotNull('finalized_at');
                });
            
            $cancelled = $cancelledQuery->count();
            
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
    private function getHiringStats($month = null, $fromDate = null, $toDate = null, $account = null)
    {
        $query = Archive::query();
        
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: archives table doesn't have account_id, so we can't filter by account here
        
        $notRecommended = (clone $query)->where('status', 'not_recommended')->count();
        $noAnswer = (clone $query)->where('status', 'no_answer_3_attempts')->count();
        $declined = (clone $query)->where('status', 'declined')->count();
        
        return [
            'not_recommended' => $notRecommended,
            'no_answer' => $noAnswer,
            'declined' => $declined
        ];
    }

    /**
     * Get active tutors count
     */
    private function getActiveTutorsCount($account = null)
    {
        $query = Tutor::where('status', 'active');
        
        if ($account) {
            // Get account ID from account name
            $accountId = Account::where('account_name', $account)->first()?->account_id;
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
        }
        
        return $query->count();
    }

    /**
     * Get tutor utilization rate (only finalized schedules - all time)
     */
    private function getTutorUtilization($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $totalTutors = $this->getActiveTutorsCount($account);
        
        $query = AssignedDailyData::whereNotNull('finalized_at')
            ->where(function($q) {
                $q->whereNotNull('main_tutor')
                  ->orWhereNotNull('backup_tutor');
            });
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        $assignedTutors = $query->distinct()
            ->count(DB::raw('COALESCE(main_tutor, backup_tutor)'));
        
        return $totalTutors > 0 ? round(($assignedTutors / $totalTutors) * 100, 1) : 0;
    }

    /**
     * Get schedule status breakdown (all time)
     */
    private function getScheduleStatusBreakdown($weekStart, $fromDate = null, $toDate = null, $account = null)
    {
        $query = AssignedDailyData::whereNotNull('finalized_at');
        
        if ($fromDate && $toDate) {
            $query->whereBetween('finalized_at', [Carbon::parse($fromDate), Carbon::parse($toDate)->endOfDay()]);
        }
        
        // Note: schedules_daily_data doesn't have account_id, so we can't filter by account here
        
        $finalized = $query->count();
        
        $notFinalizedQuery = ScheduleDailyData::whereDoesntHave('assignedData', function($q) {
            $q->whereNotNull('finalized_at');
        });
        
        $notFinalized = $notFinalizedQuery->count();
            
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

    /**
     * Get detailed applicants for modal (API endpoint)
     */
    public function getApplicantsDetails(Request $request)
    {
        try {
            $month = $request->get('month', Carbon::now()->format('Y-m'));
            $type = $request->get('type', 'applicants');
            
            $data = collect();
            
            switch($type) {
                case 'applicants':
                    $applications = Application::whereYear('application_date_time', Carbon::parse($month . '-01')->year)
                        ->whereMonth('application_date_time', Carbon::parse($month . '-01')->month)
                        ->orderBy('application_date_time', 'desc')
                        ->get();
                    
                    $data = $applications->map(function($app) {
                        $applicant = Applicant::find($app->applicant_id);
                        return [
                            'id' => $app->applicant_id,
                            'name' => $applicant ? ($applicant->first_name . ' ' . $applicant->last_name) : 'N/A',
                            'email' => $applicant->email ?? 'N/A',
                            'phone' => $applicant->phone_number ?? 'N/A',
                            'date' => Carbon::parse($app->application_date_time)->format('M d, Y'),
                            'status' => $app->status ?? 'Pending'
                        ];
                    });
                    break;
                    
                case 'demo':
                    $demos = Demo::whereNotIn('phase', ['onboarding', 'hired'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    $data = $demos->map(function($demo) {
                        $applicant = Applicant::find($demo->applicant_id);
                        return [
                            'id' => $demo->applicant_id,
                            'name' => $applicant ? ($applicant->first_name . ' ' . $applicant->last_name) : 'N/A',
                            'email' => $applicant->email ?? 'N/A',
                            'phone' => $applicant->phone_number ?? 'N/A',
                            'phase' => ucfirst($demo->phase ?? 'demo'),
                            'status' => $demo->status ?? 'Pending'
                        ];
                    });
                    break;
                    
                case 'onboarding':
                    $onboardings = Demo::where('phase', 'onboarding')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    $data = $onboardings->map(function($demo) {
                        $applicant = Applicant::find($demo->applicant_id);
                        return [
                            'id' => $demo->applicant_id,
                            'name' => $applicant ? ($applicant->first_name . ' ' . $applicant->last_name) : 'N/A',
                            'email' => $applicant->email ?? 'N/A',
                            'phone' => $applicant->phone_number ?? 'N/A',
                            'phase' => 'Onboarding',
                            'status' => $demo->status ?? 'In Progress'
                        ];
                    });
                    break;
                    
                case 'employees':
                    $tutors = Tutor::where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    $data = $tutors->map(function($tutor) {
                        $applicant = Applicant::find($tutor->applicant_id);
                        $account = DB::table('accounts')->where('account_id', $tutor->account_id)->first();
                        
                        return [
                            'id' => $tutor->tutor_id,
                            'name' => $applicant ? ($applicant->first_name . ' ' . $applicant->last_name) : 'N/A',
                            'email' => $tutor->email ?? ($applicant->email ?? 'N/A'),
                            'username' => $tutor->username ?? 'N/A',
                            'account' => $account->account_name ?? 'N/A',
                            'status' => 'Active'
                        ];
                    });
                    break;
            }
            
            return response()->json([
                'data' => $data->values(),
                'count' => $data->count(),
                'month' => Carbon::parse($month . '-01')->format('F Y')
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard getApplicantsDetails error: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'count' => 0,
                'month' => Carbon::now()->format('F Y'),
                'error' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sparkline data for cards (last 4 weeks)
     */
    public function getSparklineData(Request $request)
    {
        try {
            $type = $request->get('type', 'applicants');
            $weeks = [];
            
            for ($i = 3; $i >= 0; $i--) {
                $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                $count = 0;
                
                switch($type) {
                    case 'applicants':
                        $count = Application::whereBetween('application_date_time', [$weekStart, $weekEnd])->count();
                        break;
                    case 'demo':
                        $count = Demo::whereNotIn('phase', ['onboarding', 'hired'])
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->count();
                        break;
                    case 'onboarding':
                        $count = Demo::where('phase', 'onboarding')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->count();
                        break;
                    case 'employees':
                        $count = Tutor::where('status', 'active')
                            ->whereBetween('created_at', [$weekStart, $weekEnd])
                            ->count();
                        break;
                }
                
                $weeks[] = $count;
            }
            
            return response()->json(['data' => $weeks]);
        } catch (\Exception $e) {
            Log::error('Dashboard getSparklineData error: ' . $e->getMessage());
            return response()->json(['data' => [0, 0, 0, 0], 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get hiring status trends for the line chart
     */
    public function getHiringTrends(Request $request)
    {
        try {
            $weeks = [];
            
            for ($i = 3; $i >= 0; $i--) {
                $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                $notRecommended = Archive::where('status', 'not_recommended')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();
                
                $noAnswer = Archive::where('status', 'no_answer_3_attempts')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();
                
                $declined = Archive::where('status', 'declined')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();
                
                $weeks[] = [
                    'not_recommended' => $notRecommended,
                    'no_answer' => $noAnswer,
                    'declined' => $declined
                ];
            }
            
            return response()->json(['data' => $weeks]);
        } catch (\Exception $e) {
            Log::error('Dashboard getHiringTrends error: ' . $e->getMessage());
            return response()->json([
                'data' => [
                    ['not_recommended' => 0, 'no_answer' => 0, 'declined' => 0],
                    ['not_recommended' => 0, 'no_answer' => 0, 'declined' => 0],
                    ['not_recommended' => 0, 'no_answer' => 0, 'declined' => 0],
                    ['not_recommended' => 0, 'no_answer' => 0, 'declined' => 0]
                ],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
