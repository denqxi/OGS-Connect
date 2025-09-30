<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\DailyData;
use App\Models\TutorAvailability;
use App\Models\TutorAssignment;
use App\Models\ScheduleHistory;
use App\Models\Supervisor;
use App\Services\TutorAssignmentService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Simple index method to avoid undefined method error.
     * (Removed due to duplicate declaration)
     */
    // public function index(Request $request)
    // {
    //     return redirect()->route('schedules.index', ['tab' => 'class']);
    // }
    protected $assignmentService;

    public function __construct(TutorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Main index method - handles all tabs and views
     */
    public function index(Request $request)
    {
        try {
            $tab = $request->input('tab', 'employee');

        // Class scheduling tab
        if ($tab === 'class') {
            if ($request->has('date')) {
                return $this->showPerDaySchedule($request->date, $request->input('page', 1));
            }

            // Default class list view
            $query = DailyData::query();
            
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'finalized')
                  ->orWhereNull('schedule_status');
            });

            if ($request->filled('search')) {
                $query->where('school', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }

            if ($request->filled('day')) {
                $query->where('day', $request->day);
            }

            if ($request->filled('status')) {
                $this->applyStatusFilter($query, $request->status);
            }

            $dailyData = $query->selectRaw('date, day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 0 ELSE 1 END) as active_class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_class_count,
                SUM(number_required) as total_required,
                (SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned,
                GROUP_CONCAT(DISTINCT assigned_supervisor ORDER BY assigned_supervisor ASC SEPARATOR ", ") as assigned_supervisors')
                ->groupBy('date', 'day')
                ->orderBy('date', 'desc')
                ->paginate(5)
                ->withQueryString();

            return view('schedules.index', compact('dailyData'));
        }

        // Schedule History tab
        if ($tab === 'history') {
            return $this->showScheduleHistory($request);
        }

        // Employee availability tab
        if ($tab === 'employee') {
            $query = Tutor::with(['accounts' => function($query) {
                $query->forAccount('GLS')->active();
            }])
            ->whereHas('accounts', function($query) {
                $query->forAccount('GLS')->active();
            });
            
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('tusername', 'like', '%' . $request->search . '%')
                      ->orWhere('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('phone_number', 'like', '%' . $request->search . '%')
                      ->orWhere('applicant_id', 'like', '%' . $request->search . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->search . '%']);
                });
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('time_range')) {
                $query->whereHas('accounts', function($q) use ($request) {
                    $q->forAccount('GLS')->active();
                    
                    switch($request->time_range) {
                        case 'morning':
                            $q->where(function($timeQuery) {
                                $timeQuery->where('available_times', 'like', '%06:00%')
                                    ->orWhere('available_times', 'like', '%07:00%')
                                    ->orWhere('available_times', 'like', '%08:00%')
                                    ->orWhere('available_times', 'like', '%09:00%')
                                    ->orWhere('available_times', 'like', '%10:00%')
                                    ->orWhere('available_times', 'like', '%11:00%')
                                    ->orWhere('preferred_time_range', 'morning');
                            });
                            break;
                        case 'afternoon':
                            $q->where(function($timeQuery) {
                                $timeQuery->where('available_times', 'like', '%12:00%')
                                    ->orWhere('available_times', 'like', '%13:00%')
                                    ->orWhere('available_times', 'like', '%14:00%')
                                    ->orWhere('available_times', 'like', '%15:00%')
                                    ->orWhere('available_times', 'like', '%16:00%')
                                    ->orWhere('available_times', 'like', '%17:00%')
                                    ->orWhere('preferred_time_range', 'afternoon');
                            });
                            break;
                        case 'evening':
                            $q->where(function($timeQuery) {
                                $timeQuery->where('available_times', 'like', '%18:00%')
                                    ->orWhere('available_times', 'like', '%19:00%')
                                    ->orWhere('available_times', 'like', '%20:00%')
                                    ->orWhere('available_times', 'like', '%21:00%')
                                    ->orWhere('available_times', 'like', '%22:00%')
                                    ->orWhere('available_times', 'like', '%23:00%')
                                    ->orWhere('preferred_time_range', 'evening');
                            });
                            break;
                    }
                });
            }
            
            if ($request->filled('day')) {
                $dayName = ucfirst($request->day);
                $query->whereHas('accounts', function($q) use ($dayName) {
                    $q->forAccount('GLS')->active()
                      ->whereJsonContains('available_days', $dayName);
                });
            }
            
            $tutors = $query->paginate(5)->withQueryString();
            return view('schedules.index', compact('tutors'));
        }

        // Default redirect
        return redirect()->route('schedules.index', ['tab' => 'employee']);
        
        } catch (\Exception $e) {
            Log::error('Error in ScheduleController@index: ' . $e->getMessage());
            return redirect()->route('schedules.index', ['tab' => 'employee'])
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Get the supervisor name who finalized a schedule from schedule history
     */
    private function getScheduleSupervisorName($schedule)
    {
        Log::debug('getScheduleSupervisorName called', [
            'schedule_id' => $schedule->id,
            'schedule_class' => $schedule->class,
            'finalized_by' => $schedule->finalized_by,
            'schedule_status' => $schedule->schedule_status
        ]);
        
        // For tentative schedules, look for assignment actions first
        if ($schedule->schedule_status !== 'finalized') {
            // Look for 'assigned' actions first (who actually worked on the schedule)
            $assignmentRecord = ScheduleHistory::where('class_id', $schedule->id)
                ->where('action', 'assigned')
                ->orderBy('created_at', 'desc')
                ->first();
                
            Log::debug('Assignment history record found', [
                'assignment_record' => $assignmentRecord ? $assignmentRecord->toArray() : null
            ]);
                
            if ($assignmentRecord && $assignmentRecord->performed_by) {
                $supervisor = Supervisor::where('supID', $assignmentRecord->performed_by)->first();
                Log::debug('Supervisor found from assignment action', [
                    'performed_by' => $assignmentRecord->performed_by,
                    'supervisor' => $supervisor ? $supervisor->toArray() : null
                ]);
                if ($supervisor) {
                    return $supervisor->full_name;
                }
            }
        }
        
        // Look for the 'finalized' action in schedule history for this class
        $historyRecord = ScheduleHistory::where('class_id', $schedule->id)
            ->where('action', 'finalized')
            ->orderBy('created_at', 'desc')
            ->first();
            
        Log::debug('Finalized history record found', [
            'history_record' => $historyRecord ? $historyRecord->toArray() : null
        ]);
            
        if ($historyRecord && $historyRecord->performed_by) {
            $supervisor = Supervisor::where('supID', $historyRecord->performed_by)->first();
            Log::debug('Supervisor found from finalized action', [
                'performed_by' => $historyRecord->performed_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // If no finalized action found, look for any action that might indicate who created/finalized the schedule
        $anyHistoryRecord = ScheduleHistory::where('class_id', $schedule->id)
            ->whereIn('action', ['finalized', 'assigned', 'created', 'updated', 'exported'])
            ->orderBy('created_at', 'desc')
            ->first();
            
        Log::debug('Any history record found', [
            'history_record' => $anyHistoryRecord ? $anyHistoryRecord->toArray() : null
        ]);
            
        if ($anyHistoryRecord && $anyHistoryRecord->performed_by) {
            $supervisor = Supervisor::where('supID', $anyHistoryRecord->performed_by)->first();
            Log::debug('Supervisor found from any action', [
                'performed_by' => $anyHistoryRecord->performed_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // Fallback to finalized_by field if no history record found
        if ($schedule->finalized_by) {
            $supervisor = Supervisor::where('supID', $schedule->finalized_by)->first();
            Log::debug('Supervisor found from finalized_by field', [
                'finalized_by' => $schedule->finalized_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // Final fallback to current session supervisor
        $currentSupervisorId = session('supervisor_id');
        if (!$currentSupervisorId && Auth::guard('supervisor')->check()) {
            $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
        }
        
        if ($currentSupervisorId) {
            $supervisor = Supervisor::where('supID', $currentSupervisorId)->first();
            Log::debug('Using current session supervisor as final fallback', [
                'current_supervisor_id' => $currentSupervisorId,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        Log::debug('No supervisor found for schedule', [
            'schedule_id' => $schedule->id
        ]);
        
        return null;
    }

    /**
     * Export selected schedules to Excel (from history tab or multi-select)
     */
    public function exportSelectedSchedules(Request $request)
    {
        try {
            $request->validate([
                'dates' => 'required|array|min:1',
                'dates.*' => 'required|date'
            ]);
            
            $selectedDates = $request->input('dates');
            
            $schedules = DailyData::with(['tutorAssignments.tutor'])
                ->whereIn('date', $selectedDates)
                ->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();
                
            if ($schedules->isEmpty()) {
                $datesString = implode(', ', $selectedDates);
                return response()->json(['error' => "No schedules found for selected dates: {$datesString}"], 404);
            }
            
            // Get current supervisor for logging purposes
            $currentSupervisorId = session('supervisor_id');
            if (!$currentSupervisorId && Auth::guard('supervisor')->check()) {
                $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
            }
            foreach ($schedules as $class) {
                if (method_exists($class, 'createHistoryRecord')) {
                    $class->createHistoryRecord(
                        'exported',
                        $currentSupervisorId,
                        'Exported Selected Schedules',
                        null,
                        [
                            'export_type' => 'selected',
                            'date' => $class->date,
                            'exported_by' => $currentSupervisorId
                        ]
                    );
                }
            }
            
            // Build per-class sheets with overview (like final export but with overview)
            $spreadsheet = new Spreadsheet();
            $classSheetsData = [];
            $groupedSchedules = [];
            
            foreach ($schedules as $schedule) {
                $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y');
                $sheetKey = $date . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                Log::debug('About to call getScheduleSupervisorName for schedule', [
                    'schedule_id' => $schedule->id,
                    'schedule_class' => $schedule->class
                ]);
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);
                Log::debug('getScheduleSupervisorName returned', [
                    'schedule_id' => $schedule->id,
                    'supervisor_name' => $scheduleSupervisorName
                ]);

                // Grouped overview structure per time and school
                $slotKey = ($schedule->time_jst ?? '') . '|' . $schedule->school;
                if (!isset($groupedSchedules[$slotKey])) {
                    $groupedSchedules[$slotKey] = [
                        'schools' => [$schedule->school],
                        'date' => $schedule->date,
                        'time' => $schedule->time_jst,
                        'total_slots' => $schedule->number_required ?? 0,
                        'main_tutors' => [],
                        'backup_tutors' => []
                    ];
                } else {
                    if (!in_array($schedule->school, $groupedSchedules[$slotKey]['schools'])) {
                        $groupedSchedules[$slotKey]['schools'][] = $schedule->school;
                    }
                    // Add slots for additional classes at the same time/school
                    $groupedSchedules[$slotKey]['total_slots'] += ($schedule->number_required ?? 0);
                }

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) { continue; }

                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsArr = $glsAccount && method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                    $glsId = isset($glsArr['gls_id']) ? (string)$glsArr['gls_id'] : '';
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';

                    $tutorArr = [
                        'glsID' => $glsId,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];

                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['backup_tutors'][] = $tutor->full_name;
                    } else {
                        $mainTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['main_tutors'][] = $tutor->full_name;
                    }
                }

                if (empty($mainTutors) && $schedule->class_status === 'cancelled') {
                    $mainTutors[] = [
                        'glsID' => '',
                        'full_name' => 'CLASS CANCELLED',
                        'glsUsername' => '',
                        'glsScreenName' => '',
                        'sex' => '',
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => false,
                        'is_cancelled' => true,
                    ];
                }

                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }

            // Create overview sheet first with visualizations
            $overviewSheet = $spreadsheet->getActiveSheet();
            $this->createSelectedScheduleOverviewSheet($overviewSheet, $groupedSchedules, $schedules, null);
            $overviewSheet->setTitle('Overview');
            
            // Create per-class sheets
            $this->createClassSheets($spreadsheet, $classSheetsData, false, true);
            
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Selected_Schedules_' . now()->format('Ymd_His') . '.xlsx';
            
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting selected schedules: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    private function applyStatusFilter($query, $status)
    {
        if ($status === 'assigned') {
            $query->whereHas('tutorAssignments', function($q) {
                $q->havingRaw('COUNT(*) >= number_required');
            });
        } elseif ($status === 'unassigned') {
            $query->whereDoesntHave('tutorAssignments');
        }
    }

    private function showPerDaySchedule($date, $page = 1)
    {
        $dailyData = DailyData::where('date', $date)->with(['tutorAssignments.tutor'])->get();
        
        $finalizedSchedule = DailyData::where('date', $date)
            ->where('schedule_status', 'finalized')
            ->first();
            
        $isFinalized = $finalizedSchedule !== null;
        $finalizedAt = $finalizedSchedule ? $finalizedSchedule->finalized_at : null;
        
        return view('schedules.index', compact('dailyData', 'date', 'page', 'isFinalized', 'finalizedAt'));
    }

    /**
     * Show schedule history (finalized schedules)
     */
    private function showScheduleHistory(Request $request)
    {
        if ($request->has('view_date')) {
            $date = $request->view_date;
            $hasFinalized = DailyData::where('date', $date)
                ->where('schedule_status', 'finalized')
                ->exists();
            
            if (!$hasFinalized) {
                return redirect()->route('schedules.index', ['tab' => 'history'])
                    ->with('error', 'No finalized schedule found for this date.');
            }
            
            return view('schedules.index', compact('date'));
        }

        $query = DailyData::select([
            'date',
            'day',
            DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
            DB::raw('COUNT(*) as class_count'),
            DB::raw('SUM(CASE WHEN class_status = "cancelled" THEN 0 ELSE 1 END) as active_class_count'),
            DB::raw('SUM(CASE WHEN class_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_class_count'),
            DB::raw('SUM(number_required) as total_required'),
            DB::raw('(SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned'),
            'schedule_status',
            'finalized_at'
        ])
        ->where('schedule_status', 'finalized')
        ->groupBy('date', 'day', 'schedule_status', 'finalized_at');

        if ($request->filled('search')) {
            $query->having('schools', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        $scheduleHistory = $query->orderBy('date', 'desc')
            ->paginate(5)
            ->withQueryString();

        $availableDates = DailyData::where('schedule_status', 'finalized')
            ->select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        $availableDays = DailyData::where('schedule_status', 'finalized')
            ->select('day')
            ->distinct()
            ->pluck('day');

        return view('schedules.index', compact('scheduleHistory', 'availableDates', 'availableDays'));
    }

    /**
     * Check for time conflicts when adding a tutor to a class (AJAX endpoint)
     */
    public function checkTutorTimeConflict(Request $request)
    {
        try {
            $tutorUsername = $request->input('tutor_username');
            $classId = $request->input('class_id');
            
            if (!$tutorUsername || !$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor username and class ID are required'
                ], 400);
            }
            
            $class = DailyData::findOrFail($classId);
            $conflicts = $this->checkTimeConflicts([$tutorUsername], null, $classId, $class->date, $class->time_jst);
            
            if (!empty($conflicts)) {
                $conflictInfo = $conflicts[$tutorUsername][0];
                $role = $conflictInfo['is_backup'] ? 'backup tutor' : 'main tutor';
                
                return response()->json([
                    'success' => false,
                    'has_conflict' => true,
                    'message' => "This tutor is already assigned as {$role} to {$conflictInfo['class']} at {$conflictInfo['school']} on the same date and time",
                    'conflict_details' => $conflictInfo
                ]);
            }
            
            return response()->json([
                'success' => true,
                'has_conflict' => false
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking time conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign tutors to all available classes
     */
    public function autoAssignTutors(Request $request)
    {
        try {
            $date = $request->input('date');
            $day = $request->input('day');
            
            $result = $this->assignmentService->autoAssignTutors($date, $day);
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed. {$result['assigned']} tutors assigned.",
                'assigned' => $result['assigned'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-assignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Auto-assignment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign tutors for a specific date
     */
    public function autoAssignTutorsForDate(Request $request, $date)
    {
        try {
            $result = $this->assignmentService->autoAssignTutorsForSpecificSchedule($date, null);
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed for {$date}. {$result['assigned']} tutors assigned.",
                'assigned' => $result['assigned'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-assignment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign tutors for a specific date and day
     */
    public function autoAssignTutorsForSpecific(Request $request, $date, $day)
    {
        try {
            $result = $this->assignmentService->autoAssignTutorsForSpecificSchedule($date, $day);
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed for {$day}, {$date}. {$result['assigned']} tutors assigned.",
                'assigned' => $result['assigned'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-assignment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign tutors for a specific class
     */
    public function autoAssignTutorsForClass(Request $request, $classId)
    {
        try {
            $class = DailyData::findOrFail($classId);
            $result = $this->assignmentService->autoAssignTutorsForSpecificClass($class);
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed for {$class->class}. {$result['assigned']} tutors assigned.",
                'assigned' => $result['assigned'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-assignment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a tutor assignment
     */
    public function removeTutorAssignment(Request $request, $assignmentId)
    {
        try {
            $assignment = TutorAssignment::findOrFail($assignmentId);
            $class = $assignment->dailyData;
            
            // Check ownership
            $currentSupervisorId = null;
            if (Auth::guard('supervisor')->check()) {
                $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
            } elseif (session('supervisor_id')) {
                $currentSupervisorId = session('supervisor_id');
            }
            
            // Check if ANY class in the same schedule (same date) is owned by another supervisor
            $scheduleDate = $class->date;
            $existingOwner = DailyData::where('date', $scheduleDate)
                ->whereNotNull('assigned_supervisor')
                ->where('assigned_supervisor', '!=', $currentSupervisorId)
                ->first();
            
            if ($existingOwner) {
                return response()->json([
                    'success' => false,
                    'message' => "This schedule is being handled by another supervisor. You cannot modify it."
                ], 403);
            }
            
            $tutorName = $assignment->tutor->tusername;
            $className = $assignment->dailyData->class;
            $wasMainTutor = !$assignment->is_backup;
            $dailyDataId = $assignment->daily_data_id;
            
            $assignment->delete();
            
            $message = "Removed {$tutorName} from {$className}";
            
            if ($wasMainTutor) {
                $promotionMessage = $this->autoPromoteBackupTutor($dailyDataId, $tutorName, $className);
                if ($promotionMessage) {
                    $message .= ". " . $promotionMessage;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-promote backup tutor to main tutor for a specific class
     */
    private function autoPromoteBackupTutor($dailyDataId, $removedTutorName, $className)
    {
        $backupTutor = TutorAssignment::where('daily_data_id', $dailyDataId)
            ->where('is_backup', true)
            ->with(['tutor', 'dailyData'])
            ->first();
        
        if ($backupTutor) {
            $backupTutor->update([
                'is_backup' => false,
                'was_promoted_from_backup' => true,
                'replaced_tutor_name' => $removedTutorName,
                'promoted_at' => now()
            ]);
            
            $backupTutorName = $backupTutor->tutor->tusername;
            
            ScheduleHistory::create([
                'class_id' => $dailyDataId,
                'class_name' => $className,
                'school' => $backupTutor->dailyData->school,
                'class_date' => $backupTutor->dailyData->date,
                'class_time' => $backupTutor->dailyData->time_jst,
                'status' => $backupTutor->dailyData->schedule_status ?? 'draft',
                'action' => 'updated',
                'performed_by' => Auth::id(),
                'reason' => "Auto-promoted {$backupTutorName} from backup to main tutor after {$removedTutorName} was removed",
                'old_data' => [
                    'backup_tutor' => $backupTutorName,
                    'removed_tutor' => $removedTutorName,
                    'promotion_trigger' => 'main_tutor_removal'
                ],
                'new_data' => [
                    'main_tutor' => $backupTutorName,
                    'promotion_type' => 'automatic',
                    'promoted_at' => now()->toISOString()
                ]
            ]);
            
            return "Auto-promoted {$backupTutorName} from backup to main tutor.";
        }
        
        return null;
    }

    /**
     * Check and auto-promote backup tutors to fill vacant main tutor slots
     */
    private function checkAndAutoPromoteBackupTutors($classId)
    {
        $class = DailyData::findOrFail($classId);
        $requiredTutors = $class->number_required ?? 2;
        
        $mainCount = TutorAssignment::where('daily_data_id', $classId)
            ->where('is_backup', false)
            ->count();
            
        $backupCount = TutorAssignment::where('daily_data_id', $classId)
            ->where('is_backup', true)
            ->count();
            
        $availableSlots = $requiredTutors - $mainCount;
        
        if ($availableSlots > 0 && $backupCount > 0) {
            $backupTutorsToPromote = TutorAssignment::where('daily_data_id', $classId)
                ->where('is_backup', true)
                ->limit($availableSlots)
                ->get();
                
            $promotedCount = 0;
            foreach ($backupTutorsToPromote as $backupAssignment) {
                $backupAssignment->update([
                    'is_backup' => false,
                    'was_promoted_from_backup' => true,
                    'replaced_tutor_name' => 'Auto-filled vacant slot',
                    'promoted_at' => now()
                ]);
                $promotedCount++;
            }
            
            return $promotedCount;
        }
        
        return 0;
    }

    public function toggleTutorStatus(Request $request, Tutor $tutor)
    {
        try {
            $tutor->update(['status' => $request->status]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Tutor status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update tutor status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available tutors for dropdown
     */
    public function getAvailableTutors(Request $request)
    {
        try {
            $tutors = Tutor::where('status', 'active')
                           ->select('tutorID', 'tusername', 'email', 'first_name', 'last_name')
                           ->orderBy('first_name')
                           ->orderBy('last_name')
                           ->get()
                           ->map(function($tutor) {
                               return [
                                   'tutorID' => $tutor->tutorID,
                                   'username' => $tutor->tusername,
                                   'email' => $tutor->email,
                                   'first_name' => $tutor->first_name,
                                   'last_name' => $tutor->last_name,
                                   'full_name' => $tutor->full_name
                               ];
                           });
            
            return response()->json([
                'success' => true,
                'tutors' => $tutors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for time conflicts when assigning tutors
     */
    private function checkTimeConflicts($tutorNames, $backupTutorName, $classId, $classDate, $classTime)
    {
        $conflicts = [];
        $allTutorNames = $tutorNames;
        
        if ($backupTutorName) {
            $allTutorNames[] = $backupTutorName;
        }
        
        foreach ($allTutorNames as $tutorName) {
            if (empty(trim($tutorName))) continue;
            
            $tutor = Tutor::where('tusername', $tutorName)->first();
            if (!$tutor) continue;
            
            $conflictingAssignments = TutorAssignment::where('tutor_id', $tutor->tutorID)
                ->where('daily_data_id', '!=', $classId)
                ->whereHas('dailyData', function($query) use ($classDate, $classTime) {
                    $query->whereDate('date', $classDate)
                          ->where('time_jst', $classTime);
                })
                ->with(['dailyData'])
                ->get();
                
            if ($conflictingAssignments->count() > 0) {
                $conflictInfo = [];
                foreach ($conflictingAssignments as $assignment) {
                    $conflictInfo[] = [
                        'school' => $assignment->dailyData->school,
                        'class' => $assignment->dailyData->class,
                        'date' => $assignment->dailyData->date,
                        'time' => $assignment->dailyData->time_jst,
                        'is_backup' => $assignment->is_backup
                    ];
                }
                
                $conflicts[$tutorName] = $conflictInfo;
            }
        }
        
        return $conflicts;
    }

    /**
     * Save tutor assignments for a class
     */
    public function saveTutorAssignments(Request $request)
    {
        try {
            Log::info('Saving tutor assignments', ['request_data' => $request->all()]);
            
            $classId = $request->input('class_id');
            $tutorNames = $request->input('tutors', []);
            $backupTutor = $request->input('backup_tutor');
            
            if (!$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class ID is required'
                ], 400);
            }
            
            $class = DailyData::findOrFail($classId);
            
            // Check ownership BEFORE making any changes
            $supervisorId = null;
            if (Auth::guard('supervisor')->check()) {
                $supervisorId = Auth::guard('supervisor')->user()->supID;
            } elseif (session('supervisor_id')) {
                $supervisorId = session('supervisor_id');
            }
            
            if ($supervisorId) {
                // Check if ANY class in the same schedule (same date) is owned by another supervisor
                $scheduleDate = $class->date;
                $existingOwner = DailyData::where('date', $scheduleDate)
                    ->whereNotNull('assigned_supervisor')
                    ->where('assigned_supervisor', '!=', $supervisorId)
                    ->first();
                
                if ($existingOwner) {
                    Log::warning("Attempted to assign tutors to schedule owned by another supervisor", [
                        'class_id' => $class->id,
                        'current_supervisor' => $supervisorId,
                        'existing_owner' => $existingOwner->assigned_supervisor,
                        'schedule_date' => $scheduleDate,
                        'class_name' => $class->class
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => "This schedule is already being handled by another supervisor. You cannot modify it."
                    ], 403);
                }
            }
            
            $backupTutorName = $backupTutor ? $backupTutor['username'] : null;
            $conflicts = $this->checkTimeConflicts($tutorNames, $backupTutorName, $classId, $class->date, $class->time_jst);
            
            if (!empty($conflicts)) {
                $conflictMessages = [];
                foreach ($conflicts as $tutorName => $conflictInfo) {
                    foreach ($conflictInfo as $conflict) {
                        $role = $conflict['is_backup'] ? 'backup tutor' : 'main tutor';
                        $conflictMessages[] = "{$tutorName} is already assigned as {$role} to {$conflict['class']} at {$conflict['school']} on the same date and time";
                    }
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Time conflicts detected: ' . implode('; ', $conflictMessages),
                    'conflicts' => $conflicts
                ], 400);
            }
            
            $existingMainTutors = TutorAssignment::where('daily_data_id', $classId)
                                                ->where('is_backup', false)
                                                ->get();
            $existingBackupTutors = TutorAssignment::where('daily_data_id', $classId)
                                                  ->where('is_backup', true)
                                                  ->get();
            
            if (empty($tutorNames)) {
                Log::info('No tutors in request - preserving existing main assignments', [
                    'existing_main_count' => $existingMainTutors->count()
                ]);
                
                $assignedCount = $existingMainTutors->count();
            } else {
                Log::info('Replacing main assignments with provided tutors', [
                    'tutor_count' => count($tutorNames)
                ]);
                
                TutorAssignment::where('daily_data_id', $classId)
                              ->where('is_backup', false)
                              ->delete();
                
                $assignedCount = 0;
                foreach ($tutorNames as $tutorName) {
                    if (trim($tutorName) !== '') {
                        $tutor = Tutor::where('tusername', $tutorName)->first();
                        if ($tutor) {
                            TutorAssignment::create([
                                'daily_data_id' => $classId,
                                'tutor_id' => $tutor->tutorID,
                                'is_backup' => false,
                                'assigned_at' => now(),
                            ]);
                            $assignedCount++;
                            Log::info("Assigned main tutor: {$tutorName} to class {$classId}");
                        } else {
                            Log::warning("Tutor not found: {$tutorName}");
                        }
                    }
                }
            }
            
            if ($backupTutor && isset($backupTutor['username']) && trim($backupTutor['username']) !== '') {
                $backupTutorModel = Tutor::where('tusername', $backupTutor['username'])->first();
                
                if ($backupTutorModel) {
                    TutorAssignment::where('daily_data_id', $classId)
                                  ->where('is_backup', true)
                                  ->delete();
                    
                    $alreadyAssignedAsMain = TutorAssignment::where('daily_data_id', $classId)
                                                          ->where('tutor_id', $backupTutorModel->tutorID)
                                                          ->where('is_backup', false)
                                                          ->exists();
                    
                    if (!$alreadyAssignedAsMain) {
                        TutorAssignment::create([
                            'daily_data_id' => $classId,
                            'tutor_id' => $backupTutorModel->tutorID,
                            'is_backup' => true,
                            'assigned_at' => now(),
                        ]);
                        Log::info("Added backup tutor: {$backupTutor['username']} to class {$classId}");
                    } else {
                        Log::info("Backup tutor {$backupTutor['username']} is already assigned as main tutor");
                    }
                } else {
                    Log::warning("Backup tutor not found: {$backupTutor['username']}");
                }
            }
            
            $finalMainCount = TutorAssignment::where('daily_data_id', $classId)
                                            ->where('is_backup', false)
                                            ->count();
            $finalBackupCount = TutorAssignment::where('daily_data_id', $classId)
                                              ->where('is_backup', true)
                                              ->count();
            
            // Set schedule ownership for all classes on this date if not already assigned
            if ($supervisorId && !$class->isAssigned()) {
                $scheduleDate = $class->date;
                // Assign all classes on this date to the current supervisor
                DailyData::where('date', $scheduleDate)
                    ->whereNull('assigned_supervisor')
                    ->update([
                        'assigned_supervisor' => $supervisorId,
                        'assigned_at' => now()
                    ]);
                
                Log::info("Schedule assigned to supervisor", [
                    'class_id' => $class->id,
                    'supervisor_id' => $supervisorId,
                    'class_name' => $class->class,
                    'schedule_date' => $scheduleDate
                ]);
            }
            
            // Create history record for the assignment action
            if ($supervisorId) {
                $tutorNames = array_filter($tutorNames);
                $backupTutorName = $backupTutor ? $backupTutor['username'] : null;
                $allTutorNames = array_merge($tutorNames, $backupTutorName ? [$backupTutorName] : []);
                
                $class->createHistoryRecord(
                    'assigned',
                    $supervisorId,
                    'Tutors assigned to class',
                    [
                        'previous_main_count' => $existingMainTutors->count(),
                        'previous_backup_count' => $existingBackupTutors->count()
                    ],
                    [
                        'main_tutors' => $tutorNames,
                        'backup_tutor' => $backupTutorName,
                        'final_main_count' => $finalMainCount,
                        'final_backup_count' => $finalBackupCount,
                        'assigned_tutors' => $allTutorNames
                    ]
                );
            }
            
            $response = [
                'success' => true,
                'message' => "Successfully saved {$finalMainCount} main tutor(s) and {$finalBackupCount} backup tutor(s) for {$class->class}",
                'main_count' => $finalMainCount,
                'backup_count' => $finalBackupCount,
                'total_count' => $finalMainCount + $finalBackupCount
            ];
            
            Log::info('Tutor assignments saved successfully', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error saving tutor assignments: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tutor assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX search for schedules
     */
    public function searchSchedules(Request $request)
    {
        try {
            Log::info('Search request received:', $request->all());
            
            $query = DailyData::query();
            
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'final')
                  ->orWhereNull('schedule_status');
            });
            
            if ($request->filled('search')) {
                $query->where('school', 'like', '%' . $request->search . '%');
            }
            
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }
            
            if ($request->filled('day')) {
                $query->where('day', strtolower(substr($request->day, 0, 4)));
            }
            
            if ($request->filled('status')) {
                $this->applyStatusFilter($query, $request->status);
            }
            
            $selectRaw = 'date, day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 0 ELSE 1 END) as active_class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_class_count,
                SUM(number_required) as total_required,
                (SELECT COUNT(*) FROM tutor_assignments ta 
                 WHERE ta.daily_data_id IN (
                     SELECT dd2.id FROM daily_data dd2 
                     WHERE dd2.date = daily_data.date AND dd2.day = daily_data.day'
                 . ($request->filled('search') ? ' AND dd2.school LIKE ?' : '') . '
                 ) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned';
            
            $bindings = [];
            if ($request->filled('search')) {
                $bindings[] = '%' . $request->search . '%';
            }
            
            $dailyData = $query
                ->selectRaw($selectRaw, $bindings)
                ->groupBy('date', 'day')
                ->orderBy('date', 'desc')
                ->paginate(5)
                ->withQueryString();
            
            Log::info('Pagination URLs:', [
                'current_page' => $dailyData->currentPage(),
                'last_page' => $dailyData->lastPage(),
                'has_pages' => $dailyData->hasPages(),
                'query_string' => $request->getQueryString()
            ]);
            
            $paginationHtml = view('schedules.partials.class-pagination', ['dailyData' => $dailyData])->render();
            return response()->json([
                'success' => true,
                'html' => view('schedules.partials.class-table-rows', compact('dailyData'))->render(),
                'pagination' => $paginationHtml,
                'total' => $dailyData->total(),
                'current_page' => $dailyData->currentPage(),
                'last_page' => $dailyData->lastPage()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tutor assignments for a specific class
     */
    public function getClassTutors(Request $request, $classId)
    {
        try {
            $class = DailyData::findOrFail($classId);
            
            $mainTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where(function($query) {
                    $query->where('is_backup', false)
                          ->orWhereNull('is_backup');
                })
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_promoted' => $assignment->was_promoted_from_backup,
                        'replaced_tutor' => $assignment->replaced_tutor_name
                    ];
                });

            $backupTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('is_backup', true)
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_promoted' => $assignment->was_promoted_from_backup,
                        'replaced_tutor' => $assignment->replaced_tutor_name
                    ];
                });

            return response()->json([
                'success' => true,
                'main_tutors' => $mainTutors,
                'backup_tutors' => $backupTutors,
                'class_info' => [
                    'id' => $class->id,
                    'name' => $class->class,
                    'required' => $class->number_required
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save schedule as final for a specific date
     */
    public function saveAsFinal(Request $request, $date)
    {
        try {
            $supervisorId = session('supervisor_id');
            if (!$supervisorId && Auth::guard('supervisor')->check()) {
                $supervisorId = Auth::guard('supervisor')->user()->supID;
            }

            // Check if ANY class in the same schedule (same date) is owned by another supervisor
            $existingOwner = DailyData::where('date', $date)
                ->whereNotNull('assigned_supervisor')
                ->where('assigned_supervisor', '!=', $supervisorId)
                ->first();
            
            if ($existingOwner) {
                return response()->json([
                    'success' => false,
                    'message' => "This schedule is being handled by another supervisor. You cannot finalize it."
                ], 403);
            }

            $updated = DailyData::where('date', $date)
                ->update([
                    'schedule_status' => 'finalized',
                    'finalized_at' => now(),
                    'finalized_by' => $supervisorId
                ]);

            $classes = DailyData::where('date', $date)->get();
            foreach ($classes as $class) {
                if (method_exists($class, 'createHistoryRecord')) {
                    $oldData = [
                        'schedule_status' => $class->getOriginal('schedule_status'),
                        'finalized_at' => $class->getOriginal('finalized_at'),
                        'finalized_by' => $class->getOriginal('finalized_by'),
                    ];
                    $class->createHistoryRecord(
                        'finalized',
                        $supervisorId,
                        'Schedule finalized',
                        $oldData,
                        [
                            'schedule_status' => 'finalized',
                            'finalized_at' => now(),
                            'finalized_by' => $supervisorId
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Schedule for {$date} finalized and archived ({$updated} classes updated)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save as final: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportTentativeSchedule(Request $request)
    {
        try {
            $query = DailyData::with(['tutorAssignments.tutor' => function($q) {
                $q->with(['accounts' => function($qa) {
                    $qa->where('account_name', 'GLS')->where('status', 'active')->select(['id', 'tutor_id', 'account_name', 'gls_id', 'username', 'screen_name']);
                }]);
            }])
                ->where(function($q) {
                    $q->where('schedule_status', '!=', 'finalized')
                        ->orWhereNull('schedule_status');
                });

            if ($request->has('date') && $request->date) {
                $query->where('date', $request->date);
            }

            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();


            if ($schedules->isEmpty()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => 'No tentative schedules found for the specified criteria.'], 404);
                } else {
                    return response('No tentative schedules found for the specified criteria.', 404)
                        ->header('Content-Type', 'text/plain')
                        ->header('Content-Disposition', 'attachment; filename="Tentative_Schedule_Error.txt"');
                }
            }

            // Get current supervisor for logging purposes
            $currentSupervisorId = session('supervisor_id');
            if (!$currentSupervisorId && Auth::guard('supervisor')->check()) {
                $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
            }
            foreach ($schedules as $class) {
                if (method_exists($class, 'createHistoryRecord')) {
                    $class->createHistoryRecord(
                        'exported',
                        $currentSupervisorId,
                        'Exported Tentative Schedule',
                        null,
                        [
                            'export_type' => 'tentative',
                            'date' => $class->date,
                            'exported_by' => $currentSupervisorId
                        ]
                    );
                }
            }

            $spreadsheet = new Spreadsheet();
            // Group schedules for overview and class sheets
            $groupedSchedules = [];
            $classSheetsData = [];
            foreach ($schedules as $schedule) {
                $date = $schedule->date; // Y-m-d
                $dateFormatted = \Carbon\Carbon::parse($date)->format('F j, Y');
                $time = $schedule->time_jst ?? '';
                $key = $dateFormatted . '|' . $schedule->school . '|' . $schedule->class;
                $sheetKey = $dateFormatted . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                Log::debug('TENTATIVE: About to call getScheduleSupervisorName for schedule', [
                    'schedule_id' => $schedule->id,
                    'schedule_class' => $schedule->class
                ]);
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);
                Log::debug('TENTATIVE: getScheduleSupervisorName returned', [
                    'schedule_id' => $schedule->id,
                    'supervisor_name' => $scheduleSupervisorName
                ]);

                // Overview grouping
                $slotKey = ($schedule->time_jst ?? '') . '|' . $schedule->school;
                if (!isset($groupedSchedules[$slotKey])) {
                    $groupedSchedules[$slotKey] = [
                        'schools' => [$schedule->school],
                        'date' => $schedule->date, // Add date for overview header
                        'time' => $schedule->time_jst,
                        'total_slots' => $schedule->number_required ?? 0,
                        'main_tutors' => [],
                        'backup_tutors' => [],
                        'supervisor_name' => $scheduleSupervisorName
                    ];
                } else {
                    if (!in_array($schedule->school, $groupedSchedules[$slotKey]['schools'])) {
                        $groupedSchedules[$slotKey]['schools'][] = $schedule->school;
                    }
                    // Add slots for additional classes at the same time/school
                    $groupedSchedules[$slotKey]['total_slots'] += ($schedule->number_required ?? 0);
                }

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) continue;
                    // Fetch GLS account directly from DB for this tutor
                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsId = '';
                    if ($glsAccount) {
                        // Use toArray() to extract gls_id, since property/array access fails
                        $glsArr = method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                        $glsId = isset($glsArr['gls_id']) ? $glsArr['gls_id'] : '';
                    }
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';
                    $glsIdStr = (string)$glsId;
                    Log::debug('EXPORT_TENTATIVE: FINAL glsID before tutorArr', [
                        'glsId' => $glsId,
                        'glsIdStr' => $glsIdStr,
                        'tutor_id' => $tutor->tutorID
                    ]);
                    $tutorArr = [
                        'glsID' => $glsIdStr,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];
                    Log::debug('EXPORT_TENTATIVE: tutorArr before Excel', [
                        'tutorArr' => $tutorArr,
                        'tutor_id' => $tutor->tutorID
                    ]);
                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['backup_tutors'][] = $tutor->full_name;
                    } else {
                        $mainTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['main_tutors'][] = $tutor->full_name;
                    }
                }
                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }
            $overviewSheet = $spreadsheet->getActiveSheet();
            $this->createOverviewSheet($overviewSheet, $groupedSchedules);
            $overviewSheet->setTitle('Overview');
            $this->createClassSheets($spreadsheet, $classSheetsData, true, false);
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Tentative_Schedule_' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting tentative schedule: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to export: ' . $e->getMessage()], 500);
            } else {
                return response('Failed to export: ' . $e->getMessage(), 500)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="Tentative_Schedule_Error.txt"');
            }
        }
    }

    /**
     * Export finalized schedule to Excel (Schedule History tab)
     */
    public function exportFinalSchedule(Request $request)
    {
        // Get current supervisor for logging purposes
        $currentSupervisorId = session('supervisor_id');
        if (!$currentSupervisorId && Auth::guard('supervisor')->check()) {
            $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
        }
        // The following foreach block should be moved after $schedules is defined (inside the try block)
        try {
            $query = DailyData::with(['tutorAssignments.tutor']);
            // If a specific date is requested, export that date regardless of status;
            // otherwise export all finalized schedules
            if ($request->has('date') && $request->date) {
                $query->whereDate('date', $request->date);
            } else {
                $query->where('schedule_status', 'finalized');
            }
            
            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json(['error' => 'No finalized schedules found for the specified criteria'], 404);
            }

            // Build per-class sheets only (no overview) with cancelled markings
            $spreadsheet = new Spreadsheet();
            $classSheetsData = [];
            foreach ($schedules as $schedule) {
                $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y');
                $sheetKey = $date . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) { continue; }

                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsArr = $glsAccount && method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                    $glsId = isset($glsArr['gls_id']) ? (string)$glsArr['gls_id'] : '';
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';

                    $tutorArr = [
                        'glsID' => $glsId,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];

                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                    } else {
                        $mainTutors[] = $tutorArr;
                    }
                }

                if (empty($mainTutors) && $schedule->class_status === 'cancelled') {
                    $mainTutors[] = [
                        'glsID' => '',
                        'full_name' => 'CLASS CANCELLED',
                        'glsUsername' => '',
                        'glsScreenName' => '',
                        'sex' => '',
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => false,
                        'is_cancelled' => true,
                    ];
                }

                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }

            $this->createClassSheets($spreadsheet, $classSheetsData, false, true);
            // Remove the default empty sheet
            if ($spreadsheet->getSheetCount() > 1) {
                $spreadsheet->removeSheetByIndex(0);
                $spreadsheet->setActiveSheetIndex(0);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'finalized_schedule_' . date('Y-m-d_H-i-s') . '.xlsx';
            return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting final schedule: ' . $e->getMessage());
            return back()->with('error', 'Failed to export: ' . $e->getMessage());
        }
    }

    /**
     * Generate Excel file based on the Google Sheets format shown in the image
     */
    private function generateExcel($schedules, $title)
    {
        // This method is no longer used - keeping for backward compatibility
        // All exports now go directly through their specific methods
        return null;
    }

    /**
     * Generate finalized schedule Excel with detailed tutor list format
     */
    private function generateFinalizedScheduleExcel($spreadsheet, $schedules, $title, $editable = false, $showCancelledMarkings = false, $supervisorName = null)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Overview');
        
        $groupedSchedules = [];
        $classSheetsData = [];
        
        foreach ($schedules as $schedule) {
            $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y');
            $sheetKey = $date . ' - ' . $schedule->school . ' - ' . $schedule->class;
            
            // Get the supervisor who finalized this schedule
            $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);

            // Grouped overview structure per time and school
            $slotKey = ($schedule->time_jst ?? '') . '|' . $schedule->school;
            if (!isset($groupedSchedules[$slotKey])) {
                $groupedSchedules[$slotKey] = [
                    'schools' => [$schedule->school],
                    'date' => $schedule->date,
                    'time' => $schedule->time_jst,
                    'total_slots' => $schedule->number_required ?? 0,
                    'main_tutors' => [],
                    'backup_tutors' => []
                ];
            } else {
                if (!in_array($schedule->school, $groupedSchedules[$slotKey]['schools'])) {
                    $groupedSchedules[$slotKey]['schools'][] = $schedule->school;
                }
                // Add slots for additional classes at the same time/school
                $groupedSchedules[$slotKey]['total_slots'] += ($schedule->number_required ?? 0);
            }

            // Build class sheet tutor data
            $mainTutors = [];
            $backupTutors = [];
            foreach ($schedule->tutorAssignments as $assignment) {
                $tutor = $assignment->tutor;
                if (!$tutor) { continue; }

                $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                $glsArr = $glsAccount && method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                $glsId = isset($glsArr['gls_id']) ? (string)$glsArr['gls_id'] : '';
                $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';

                $tutorArr = [
                    'glsID' => $glsId,
                    'full_name' => $tutor->full_name,
                    'glsUsername' => $glsUsername,
                    'glsScreenName' => $glsScreenName,
                    'sex' => $tutor->sex,
                    'supervisor' => $scheduleSupervisorName,
                    'is_backup' => $assignment->is_backup,
                    'is_cancelled' => $schedule->class_status === 'cancelled',
                ];

                if ($assignment->is_backup) {
                    $backupTutors[] = $tutorArr;
                    $groupedSchedules[$slotKey]['backup_tutors'][] = $tutor->full_name;
                } else {
                    $mainTutors[] = $tutorArr;
                    $groupedSchedules[$slotKey]['main_tutors'][] = $tutor->full_name;
                }
            }

            if (empty($mainTutors) && $schedule->class_status === 'cancelled') {
                $mainTutors[] = [
                    'glsID' => '',
                    'full_name' => 'CLASS CANCELLED',
                    'glsUsername' => '',
                    'glsScreenName' => '',
                    'sex' => '',
                    'supervisor' => $scheduleSupervisorName,
                    'is_backup' => false,
                    'is_cancelled' => true,
                ];
            }

            $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
        }

        $this->createOverviewSheet($sheet, $groupedSchedules);
        $this->createClassSheets($spreadsheet, $classSheetsData, $editable, $showCancelledMarkings);
        $this->finalizeAndOutputEditableExcel($spreadsheet, $title);
    }

    /**
     * Create enhanced overview sheet for selected schedules with visualizations
     */
    private function createSelectedScheduleOverviewSheet($sheet, $groupedSchedules, $schedules, $supervisorName = null)
    {
        // Calculate summary statistics
        $totalClasses = count($schedules);
        $totalSlots = array_sum(array_column($groupedSchedules, 'total_slots'));
        $totalMainTutors = 0;
        $totalBackupTutors = 0;
        $schools = [];
        $timeSlots = [];
        
        foreach ($groupedSchedules as $data) {
            $totalMainTutors += count($data['main_tutors'] ?? []);
            $totalBackupTutors += count($data['backup_tutors'] ?? []);
            $schools = array_merge($schools, $data['schools'] ?? []);
            if (!empty($data['time'])) {
                $timeSlots[] = $data['time'];
            }
        }
        
        $uniqueSchools = array_unique($schools);
        $uniqueTimeSlots = array_unique($timeSlots);
        $fillRate = $totalSlots > 0 ? round(($totalMainTutors / $totalSlots) * 100, 1) : 0;
        
        // Format time slots as strings for display (convert JST to PHT)
        $formattedTimeSlots = array_map(function($time) {
            return $this->convertJstToPht($time);
        }, $uniqueTimeSlots);
        
        // Set column widths for overview sheet
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(25);
        
        // Create summary section at the top
        $this->createSummarySection($sheet, $totalClasses, $totalSlots, $totalMainTutors, $totalBackupTutors, count($uniqueSchools), $formattedTimeSlots, $fillRate);
        
        // Create visualizations section
        $this->createVisualizationSection($sheet, $groupedSchedules, $uniqueSchools, $uniqueTimeSlots);
        
        // Create insights section
        $this->createInsightsSection($sheet, $groupedSchedules, $totalSlots, $totalMainTutors, $fillRate);
        
        // Note: Detailed schedule matrix removed from overview as requested
    }
    
    /**
     * Create summary statistics section
     */
    private function createSummarySection($sheet, $totalClasses, $totalSlots, $totalMainTutors, $totalBackupTutors, $uniqueSchools, $formattedTimeSlots, $fillRate)
    {
        // Title
        $sheet->setCellValue('A1', 'SCHEDULE OVERVIEW & ANALYTICS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '2A5382']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        // Summary statistics
        $stats = [
            ['Total Classes', $totalClasses],
            ['Total Slots Available', $totalSlots],
            ['Main Tutors Assigned', $totalMainTutors],
            ['Backup Tutors Assigned', $totalBackupTutors],
            ['Schools Involved', $uniqueSchools],
            ['Time Slots', implode(', ', $formattedTimeSlots)],
            ['Fill Rate', $fillRate . '%']
        ];
        
        $row = 3;
        foreach ($stats as $index => $stat) {
            $col = $index < 4 ? 'A' : 'E'; // First 4 stats in column A, rest in column E
            $statRow = $index < 4 ? $row + $index : $row + ($index - 4);
            
            $sheet->setCellValue($col . $statRow, $stat[0] . ':');
            $sheet->setCellValue(chr(ord($col) + 1) . $statRow, $stat[1]);
            
            $sheet->getStyle($col . $statRow)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            
            $fillColor = $stat[0] === 'Fill Rate' ? 
                ($fillRate >= 90 ? '90EE90' : ($fillRate >= 70 ? 'FFE4B5' : 'FFB6C1')) : 'F0F8FF';
            
            $sheet->getStyle(chr(ord($col) + 1) . $statRow)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
        }
    }
    
    /**
     * Create visualization section with charts and graphs
     */
    private function createVisualizationSection($sheet, $groupedSchedules, $uniqueSchools, $uniqueTimeSlots)
    {
        $startRow = 10;
        
        // School distribution chart
        $sheet->setCellValue('A' . $startRow, 'SCHOOL DISTRIBUTION');
        $sheet->mergeCells('A' . $startRow . ':D' . $startRow);
        $sheet->getStyle('A' . $startRow . ':D' . $startRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE4B5']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row = $startRow + 1;
        foreach ($uniqueSchools as $school) {
            $classCount = 0;
            $tutorCount = 0;
            foreach ($groupedSchedules as $data) {
                if (in_array($school, $data['schools'] ?? [])) {
                    $classCount++;
                    $tutorCount += count($data['main_tutors'] ?? []);
                }
            }
            
            $sheet->setCellValue('A' . $row, $school);
            $sheet->setCellValue('B' . $row, $classCount . ' classes');
            $sheet->setCellValue('C' . $row, $tutorCount . ' tutors');
            
            // Create a visual bar chart using cell background colors and text
            $barLength = min(20, max(1, $tutorCount));
            $bar = str_repeat('█', $barLength);
            $sheet->setCellValue('D' . $row, $bar);
            
            // Add background color to make the bar more visible
            $barColor = $tutorCount > 15 ? '90EE90' : ($tutorCount > 10 ? 'FFE4B5' : 'FFB6C1');
            $sheet->getStyle('D' . $row)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $barColor]],
                'font' => ['color' => ['rgb' => '000000'], 'size' => 10]
            ]);
            
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
                    $row++;
        }
        
        // Time slot distribution
        $sheet->setCellValue('F' . $startRow, 'TIME SLOT DISTRIBUTION');
        $sheet->mergeCells('F' . $startRow . ':H' . $startRow);
        $sheet->getStyle('F' . $startRow . ':H' . $startRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6E6FA']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row = $startRow + 1;
        foreach ($uniqueTimeSlots as $time) {
            $classCount = 0;
            $tutorCount = 0;
            $timeStr = $this->convertJstToPht($time);
            foreach ($groupedSchedules as $data) {
                $dataTimeStr = $this->convertJstToPht($data['time']);
                if ($dataTimeStr === $timeStr) {
                    $classCount++;
                    $tutorCount += count($data['main_tutors'] ?? []);
                }
            }
            
            $sheet->setCellValue('F' . $row, $timeStr);
            $sheet->setCellValue('G' . $row, $classCount . ' classes');
            
            // Create a visual bar chart with background colors
            $barLength = min(15, max(1, $tutorCount));
            $bar = str_repeat('█', $barLength);
            $sheet->setCellValue('H' . $row, $bar);
            
            // Add background color to make the bar more visible
            $barColor = $tutorCount > 8 ? '90EE90' : ($tutorCount > 5 ? 'FFE4B5' : 'FFB6C1');
            $sheet->getStyle('H' . $row)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $barColor]],
                'font' => ['color' => ['rgb' => '000000'], 'size' => 10]
            ]);
            
            $sheet->getStyle('F' . $row . ':H' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                    ]);
                    $row++;
        }
    }
    
    /**
     * Create insights and recommendations section
     */
    private function createInsightsSection($sheet, $groupedSchedules, $totalSlots, $totalMainTutors, $fillRate)
    {
        $startRow = 20;
        
        // Insights header
        $sheet->setCellValue('A' . $startRow, 'INSIGHTS & RECOMMENDATIONS');
        $sheet->mergeCells('A' . $startRow . ':H' . $startRow);
        $sheet->getStyle('A' . $startRow . ':H' . $startRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'DDA0DD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row = $startRow + 1;
        $insights = [];
        
        // Generate insights based on data
        if ($fillRate >= 100) {
            $insights[] = "✅ Excellent! All slots are fully assigned.";
        } elseif ($fillRate >= 90) {
            $insights[] = "✅ Very good fill rate. Only " . ($totalSlots - $totalMainTutors) . " slots remaining.";
        } elseif ($fillRate >= 70) {
            $insights[] = "⚠️ Moderate fill rate. " . ($totalSlots - $totalMainTutors) . " slots still need assignment.";
        } else {
            $insights[] = "❌ Low fill rate. " . ($totalSlots - $totalMainTutors) . " slots need urgent attention.";
        }
        
        // Check for backup tutor coverage
        $totalBackupTutors = 0;
        foreach ($groupedSchedules as $data) {
            $totalBackupTutors += count($data['backup_tutors'] ?? []);
        }
        
        if ($totalBackupTutors > 0) {
            $insights[] = "🔄 " . $totalBackupTutors . " backup tutors available for coverage.";
        } else {
            $insights[] = "⚠️ No backup tutors assigned. Consider adding backup coverage.";
        }
        
        // Check for time distribution
        $timeSlotCounts = [];
        foreach ($groupedSchedules as $data) {
            if (!empty($data['time'])) {
                $timeKey = is_object($data['time']) ? $data['time']->format('H:i:s') : (string)$data['time'];
                $timeSlotCounts[$timeKey] = ($timeSlotCounts[$timeKey] ?? 0) + 1;
            }
        }
        
        if (count($timeSlotCounts) > 1) {
            $maxTime = array_keys($timeSlotCounts, max($timeSlotCounts))[0];
            $insights[] = "📊 Peak time slot: " . $maxTime . " (" . max($timeSlotCounts) . " classes)";
        }
        
        // Display insights
        foreach ($insights as $index => $insight) {
            $col = $index < 2 ? 'A' : 'E'; // First 2 insights in column A, rest in column E
            $insightRow = $index < 2 ? $row + $index : $row + ($index - 2);
            
            $sheet->setCellValue($col . $insightRow, $insight);
            $sheet->mergeCells($col . $insightRow . ':' . chr(ord($col) + 2) . $insightRow);
            
            $sheet->getStyle($col . $insightRow . ':' . chr(ord($col) + 2) . $insightRow)->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'F8F8FF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
        }
    }
    
    /**
     * Create the main schedule matrix
     */
    private function createScheduleMatrix($sheet, $groupedSchedules, $startRow)
    {
        // Add a separator
        $sheet->setCellValue('A' . ($startRow - 1), 'DETAILED SCHEDULE MATRIX');
        $sheet->mergeCells('A' . ($startRow - 1) . ':H' . ($startRow - 1));
        $sheet->getStyle('A' . ($startRow - 1) . ':H' . ($startRow - 1))->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'D3D3D3']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        // Matrix layout: each column is a slot (school/time), with headers, main tutors, BACK UP row, backup tutors
        $columnIndex = 1;
        $maxMain = 0;
        $maxBackup = 0;
        foreach ($groupedSchedules as $slotKey => $data) {
            $mainCount = count($data['main_tutors'] ?? []);
            $backupCount = count($data['backup_tutors'] ?? []);
            if ($mainCount > $maxMain) $maxMain = $mainCount;
            if ($backupCount > $maxBackup) $maxBackup = $backupCount;
        }
        $mainRows = $maxMain;
        $backupRows = $maxBackup;
        $totalRows = 3 + $mainRows + 1 + 1 + $backupRows; // 3 header rows, main tutors, gap, BACK UP, backup tutors

        foreach ($groupedSchedules as $slotKey => $data) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $currentRow = $startRow;
            
            // Row 1: School name (FFFACD)
            $sheet->setCellValue($col . $currentRow, $data['schools'][0] ?? '');
            $sheet->getStyle($col . $currentRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFACD']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            $currentRow++;
            
            // Row 2: Date (Time) (ADD8E6)
            $dateStr = '';
            if (!empty($data['date']) && !empty($data['time'])) {
                try {
                    $dateObj = \Carbon\Carbon::parse($data['date']);
                    $jstTime = $data['time'];
                    $timeObj = null;
                    try {
                        $timeObj = \Carbon\Carbon::parse($jstTime);
                    } catch (\Exception $e) {
                        try {
                            $timeObj = \Carbon\Carbon::createFromFormat('H:i:s', $jstTime);
                        } catch (\Exception $e2) {
                            try {
                                $timeObj = \Carbon\Carbon::createFromFormat('H:i', $jstTime);
                            } catch (\Exception $e3) {
                                $timeObj = null;
                            }
                        }
                    }
                    if ($timeObj) {
                        $phTimeObj = $timeObj->copy()->subHour();
                        $dateStr = $dateObj->format('M j') . ' (' . ltrim($phTimeObj->format('g:ia'), '0') . ')';
                    }
                } catch (\Exception $e) {
                    $dateStr = '';
                }
            }
            $sheet->setCellValue($col . $currentRow, $dateStr);
            $sheet->getStyle($col . $currentRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'ADD8E6']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            $currentRow++;
            
            // Row 3: Slot count (E6F3FF)
            $sheet->setCellValue($col . $currentRow, '(' . ($data['total_slots'] ?? 0) . ' Slots)');
            $sheet->getStyle($col . $currentRow)->applyFromArray([
                'font' => ['italic' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            $currentRow++;

            // Main tutors
            $mainTutors = $data['main_tutors'] ?? [];
            for ($i = 0; $i < $mainRows; $i++) {
                $sheet->setCellValue($col . $currentRow, $mainTutors[$i] ?? '');
                $sheet->getStyle($col . $currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
                $currentRow++;
            }

            // Gap
            $currentRow++;
            $currentRow++;

            // BACK UP row
            $sheet->setCellValue($col . $currentRow, 'BACK UP');
            $sheet->getStyle($col . $currentRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE599']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]   
            ]);
            $currentRow++;

            // Backup tutors
            $backupTutors = $data['backup_tutors'] ?? [];
            for ($i = 0; $i < $backupRows; $i++) {
                $sheet->setCellValue($col . $currentRow, $backupTutors[$i] ?? '');
                $sheet->getStyle($col . $currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6CC']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
                $currentRow++;
            }

            // Set column width
            $sheet->getColumnDimension($col)->setWidth(18);
            $columnIndex++;
        }
        
        // Freeze the top rows
        $sheet->freezePane('A' . ($startRow + 3));
    }

    /**
     * Create the overview sheet with the existing format
     */
    private function createOverviewSheet($sheet, $groupedSchedules)
    {
        // Matrix layout: each column is a slot (school/time), with headers, main tutors, BACK UP row, backup tutors
        $columnIndex = 1;
        $maxMain = 0;
        $maxBackup = 0;
        foreach ($groupedSchedules as $slotKey => $data) {
            $mainCount = count($data['main_tutors'] ?? []);
            $backupCount = count($data['backup_tutors'] ?? []);
            if ($mainCount > $maxMain) $maxMain = $mainCount;
            if ($backupCount > $maxBackup) $maxBackup = $backupCount;
        }
        $mainRows = $maxMain;
        $backupRows = $maxBackup;
    $totalRows = 3 + $mainRows + 1 + 1 + $backupRows; // 3 header rows, main tutors, gap, BACK UP, backup tutors

    foreach ($groupedSchedules as $slotKey => $data) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
        // Row 1: School name (FFFACD)
        $sheet->setCellValue($col.'1', $data['schools'][0] ?? '');
        $sheet->getStyle($col.'1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFACD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        // Row 2: Date (Time) (ADD8E6)
        // Format: Sep 5 (8:05am) -- PH time, not JST
        // Always show 'M j (g:ia)' using date and time from $data, fallback to blank if either missing
        $dateStr = '';
        if (!empty($data['date']) && !empty($data['time'])) {
            try {
                $dateObj = \Carbon\Carbon::parse($data['date']);
                $jstTime = $data['time'];
                $timeObj = null;
                try {
                    $timeObj = \Carbon\Carbon::parse($jstTime);
                } catch (\Exception $e) {
                    try {
                        $timeObj = \Carbon\Carbon::createFromFormat('H:i:s', $jstTime);
                    } catch (\Exception $e2) {
                        try {
                            $timeObj = \Carbon\Carbon::createFromFormat('H:i', $jstTime);
                        } catch (\Exception $e3) {
                            $timeObj = null;
                        }
                    }
                }
                if ($timeObj) {
                    $phTimeObj = $timeObj->copy()->subHour();
                    $dateStr = $dateObj->format('M j') . ' (' . ltrim($phTimeObj->format('g:ia'), '0') . ')';
                } else {
                    $dateStr = '';
                }
            } catch (\Exception $e) {
                $dateStr = '';
            }
        } else {
            $dateStr = '';
        }
        $sheet->setCellValue($col.'2', $dateStr);
        $sheet->getStyle($col.'2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'ADD8E6']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        // Row 3: Slot count (E6F3FF)
        $sheet->setCellValue($col.'3', '(' . ($data['total_slots'] ?? 0) . ' Slots)');
        $sheet->getStyle($col.'3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 11],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        // Main tutors (rows 4 to 4+mainRows-1)
        $mainTutors = $data['main_tutors'] ?? [];
        for ($i = 0; $i < $mainRows; $i++) {
            $row = 4 + $i;
            $sheet->setCellValue($col.$row, $mainTutors[$i] ?? '');
            $sheet->getStyle($col.$row)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
        }

        // 2-row gap between main tutors and BACK UP (no border)
        $gapRow1 = 4 + $mainRows;
        $gapRow2 = $gapRow1 + 1;
        $sheet->setCellValue($col.$gapRow1, '');
        $sheet->setCellValue($col.$gapRow2, '');
        $sheet->getStyle($col.$gapRow1)->applyFromArray([
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]
        ]);
        $sheet->getStyle($col.$gapRow2)->applyFromArray([
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]
        ]);

        // BACK UP row (row after gap)
        $backupHeaderRow = $gapRow2 + 1;
        $sheet->setCellValue($col.$backupHeaderRow, 'BACK UP');
        $sheet->getStyle($col.$backupHeaderRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE599']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]   
        ]);

        // Backup tutors (rows after BACK UP)
        $backupTutors = $data['backup_tutors'] ?? [];
        for ($i = 0; $i < $backupRows; $i++) {
            $row = $backupHeaderRow + 1 + $i;
            $sheet->setCellValue($col.$row, $backupTutors[$i] ?? '');
            $sheet->getStyle($col.$row)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6CC']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
        }

        // Fill any remaining empty cells to ensure all columns have the same number of rows
        for ($row = $backupHeaderRow + 1 + $backupRows; $row <= $totalRows; $row++) {
            $sheet->setCellValue($col.$row, '');
            $sheet->getStyle($col.$row)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
        }

        // Set column width
        $sheet->getColumnDimension($col)->setWidth(20);

        $columnIndex++;
    }
    
    // Set additional column widths for better readability
    $sheet->getColumnDimension('A')->setWidth(15);
    $sheet->getColumnDimension('B')->setWidth(15);
    
    // Freeze the top 3 header rows
    $sheet->freezePane('A4');
    }

    /**
     * Create individual class sheets with GLS tutor information
     */
    private function createClassSheets($spreadsheet, $classSheetsData, $editable = false, $showCancelledMarkings = true)
    {
        foreach ($classSheetsData as $className => $tutorData) {
            $classSheet = $spreadsheet->createSheet();
            $classSheet->setTitle($this->sanitizeSheetName($className));
            
            $isCancelled = !empty($tutorData) && ($tutorData[0]['is_cancelled'] ?? false) && $showCancelledMarkings;
            
            $classSheet->setCellValue('A1', 'No.');
            $classSheet->setCellValue('B1', 'glsID');
            $classSheet->setCellValue('C1', 'Full Name');
            $classSheet->setCellValue('D1', 'glsUsername');
            $classSheet->setCellValue('E1', 'glsScreenName');
            $classSheet->setCellValue('F1', 'Sex');
            $headerColor = $isCancelled ? 'FF9999' : 'ADD8E6';
            $classSheet->getStyle('A1:F1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $headerColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            if ($isCancelled) {
                $row = 2;
                $classSheet->setCellValue('A' . $row, 'CLASS CANCELLED');
                $classSheet->mergeCells('A' . $row . ':G' . $row);
                $classSheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FF0000']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THICK]]
                ]);
                $row += 2;
                
                if (count($tutorData) === 1 && $tutorData[0]['full_name'] === 'CLASS CANCELLED') {
                    $classSheet->getColumnDimension('A')->setAutoSize(true);
                    continue;
                }
            } else {
                $row = 2;
            }

            $number = 1;
            $mainTutors = array_filter($tutorData, function($t) { return empty($t['is_backup']); });
            $backupTutors = array_filter($tutorData, function($t) { return !empty($t['is_backup']); });

            foreach ($mainTutors as $tutor) {
                $classSheet->setCellValue('A' . $row, $number);
                $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                $classSheet->setCellValue('F' . $row, $tutor['sex']);
                $backgroundColor = ($isCancelled && $showCancelledMarkings) ? 'F0F0F0' : 'FFFFFF';
                $fontColor = ($isCancelled && $showCancelledMarkings) ? '808080' : '000000';
                $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backgroundColor]],
                    'font' => ['color' => ['rgb' => $fontColor]]
                ]);
                $row++;
                $number++;
            }
            
            if (count($backupTutors) > 0) {
                $row++;
                $classSheet->setCellValue('A' . $row, 'BACKUP TUTORS');
                $classSheet->mergeCells('A' . $row . ':F' . $row);
                $backupHeaderColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFF2CC';
                $backupFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000';
                $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $backupFontColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupHeaderColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $row++;
                foreach ($backupTutors as $tutor) {
                    $classSheet->setCellValue('A' . $row, $number);
                    $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                    $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                    $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                    $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                    $classSheet->setCellValue('F' . $row, $tutor['sex']);
                    $backupRowColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFE6CC';
                    $backupRowFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000';
                    $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupRowColor]],
                        'font' => ['color' => ['rgb' => $backupRowFontColor]]
                    ]);
                    $row++;
                    $number++;
                }
            }

			// Gap after backup tutors
			$row++;

			// Supervisor section below backup tutors
			$classSheet->setCellValue('A' . $row, 'SUPERVISOR');
			$classSheet->mergeCells('A' . $row . ':F' . $row);
			$classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
				'font' => ['bold' => true],
				'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
				'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
				'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
			]);
			$row++;
            $supervisorName = null;
            if (!empty($mainTutors)) {
                $firstMain = reset($mainTutors);
                if (is_array($firstMain) && isset($firstMain['supervisor'])) {
                    $supervisorName = $firstMain['supervisor'];
                }
            }
            if (!$supervisorName && !empty($backupTutors)) {
                $firstBackup = reset($backupTutors);
                if (is_array($firstBackup) && isset($firstBackup['supervisor'])) {
                    $supervisorName = $firstBackup['supervisor'];
                }
            }
            if (!$supervisorName && !empty($tutorData)) {
                $firstAny = reset($tutorData);
                if (is_array($firstAny) && isset($firstAny['supervisor'])) {
                    $supervisorName = $firstAny['supervisor'];
                }
            }
			$classSheet->setCellValue('A' . $row, $supervisorName ?: '');
            $classSheet->mergeCells('A' . $row . ':F' . $row);
            $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
				'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
				'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
			]);

            // Set optimized column widths for class sheets
            $classSheet->getColumnDimension('A')->setWidth(8);   // No.
            $classSheet->getColumnDimension('B')->setWidth(12);  // glsID
            $classSheet->getColumnDimension('C')->setWidth(20);  // Full Name
            $classSheet->getColumnDimension('D')->setWidth(15);  // glsUsername
            $classSheet->getColumnDimension('E')->setWidth(15);  // glsScreenName
            $classSheet->getColumnDimension('F')->setWidth(8);   // Sex
            
            if (!$editable) {
                $classSheet->getProtection()->setSheet(true);
            }
        }
    }

    /**
     * Stub for generateTentativeScheduleExcel to resolve undefined method error.
     * Replace with actual logic as needed.
     */
    private function generateTentativeScheduleExcel($spreadsheet, $schedules, $title)
    {
        // For now, just call the finalized schedule Excel generator with editable = true, showCancelledMarkings = true
        $this->generateFinalizedScheduleExcel($spreadsheet, $schedules, $title, true, true);
    }

    /**
     * Sanitize sheet name for Excel compatibility
     */
    private function sanitizeSheetName($name)
    {
        $name = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '-', $name);
        return substr($name, 0, 31);
    }

    /**
     * Finalize and output the Excel file (editable version for both tentative and finalized schedules)
     */
    private function finalizeAndOutputEditableExcel($spreadsheet, $title)
    {
        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

        $filename = str_replace(' ', '_', $title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        
        // Clean output buffer before sending file
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Finalize and output the Excel file (protected version for tentative schedules)
     */
    private function finalizeAndOutputExcel($spreadsheet, $title)
    {
        $spreadsheet->setActiveSheetIndex(0);
        
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setSelectLockedCells(true);
            $sheet->getProtection()->setSelectUnlockedCells(true);
        }

        $filename = str_replace(' ', '_', $title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Cancel a class (mark as cancelled and remove tutor assignments)
     */
    public function cancelClass($classId)
    {
        try {
            $class = DailyData::findOrFail($classId);
            
            // Check ownership
            $currentSupervisorId = null;
            if (Auth::guard('supervisor')->check()) {
                $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
            } elseif (session('supervisor_id')) {
                $currentSupervisorId = session('supervisor_id');
            }
            
            // Check if ANY class in the same schedule (same date) is owned by another supervisor
            $scheduleDate = $class->date;
            $existingOwner = DailyData::where('date', $scheduleDate)
                ->whereNotNull('assigned_supervisor')
                ->where('assigned_supervisor', '!=', $currentSupervisorId)
                ->first();
            
            if ($existingOwner) {
                return response()->json([
                    'success' => false,
                    'message' => "This schedule is being handled by another supervisor. You cannot modify it."
                ], 403);
            }
            
            DB::beginTransaction();
            
            $class->update([
                'class_status' => 'cancelled',
                'cancelled_at' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Class cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cancelling class: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save schedule with specified status (tentative or final)
     */
    public function saveSchedule(Request $request)
    {
        try {
            $date = $request->input('date');
            $status = $request->input('status');

            $supervisor = Supervisor::first();
            $performedBy = $supervisor ? $supervisor->supID : null;

            if (!$date || !in_array($status, ['tentative', 'final'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date or status provided'
                ], 400);
            }

            $classes = DailyData::where('date', $date)->get();

            if ($classes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No classes found for the specified date'
                ], 404);
            }

            DB::beginTransaction();

            $updatedCount = 0;
            $scheduleStatus = $status === 'final' ? 'finalized' : 'tentative';

            foreach ($classes as $class) {
                $oldData = [
                    'schedule_status' => $class->schedule_status,
                    'finalized_at' => $class->finalized_at,
                    'finalized_by' => $class->finalized_by
                ];

                $updateData = ['schedule_status' => $scheduleStatus];
                if ($status === 'final') {
                    $updateData['finalized_at'] = now();
                    $updateData['finalized_by'] = $performedBy;
                }

                $class->update($updateData);

                if (method_exists($class, 'createHistoryRecord')) {
                    $class->createHistoryRecord(
                        $status === 'final' ? 'finalized' : 'updated',
                        $performedBy,
                        "Schedule saved as " . ($status === 'final' ? 'finalized' : 'tentative'),
                        $oldData,
                        [
                            'schedule_status' => $scheduleStatus,
                            'finalized_at' => $updateData['finalized_at'] ?? null,
                            'finalized_by' => $updateData['finalized_by'] ?? null
                        ]
                    );
                }

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully saved {$updatedCount} class(es) as " . ($status === 'final' ? 'final' : 'tentative'),
                'updated_count' => $updatedCount,
                'status' => $scheduleStatus
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving schedule: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show schedule history with audit trail
     */
    public function history(Request $request)
    {
        $query = ScheduleHistory::with(['dailyData', 'performer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('class_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('class_date', '<=', $request->date_to);
        }

        if ($request->filled('school')) {
            $query->where('school', 'like', '%' . $request->school . '%');
        }

        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        $histories = $query->paginate(50);
        $users = User::orderBy('name')->get();

        return view('schedules.history', compact('histories', 'users'));
    }

    /**
     * Export schedule history as CSV
     */
    public function exportHistory(Request $request)
    {
        $query = ScheduleHistory::with(['dailyData', 'performer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('class_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('class_date', '<=', $request->date_to);
        }
        if ($request->filled('school')) {
            $query->where('school', 'like', '%' . $request->school . '%');
        }
        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        $histories = $query->get();

        $filename = 'schedule_history_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($histories) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Class Name',
                'School',
                'Class Date',
                'Class Time',
                'Status',
                'Action',
                'Performed By',
                'Reason',
                'Action Date',
                'Old Data',
                'New Data'
            ]);

            foreach ($histories as $history) {
                fputcsv($file, [
                    $history->id,
                    $history->class_name,
                    $history->school,
                    $history->class_date->format('Y-m-d'),
                    $history->class_time ? $history->class_time->format('H:i:s') : '',
                    $history->status,
                    $history->action,
                    $history->performer ? $history->performer->name : 'System',
                    $history->reason ?: '',
                    $history->created_at->format('Y-m-d H:i:s'),
                    $history->old_data ? json_encode($history->old_data) : '',
                    $history->new_data ? json_encode($history->new_data) : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Convert JST time to PHT time
     * JST is UTC+9, PHT is UTC+8, so we subtract 1 hour
     */
    private function convertJstToPht($time)
    {
        if (empty($time)) {
            return '';
        }

        try {
            $timeStr = (string)$time;
            
            // Debug: Log the original time
            Log::debug('Converting JST to PHT', [
                'original_time' => $time,
                'time_string' => $timeStr,
                'type' => gettype($time)
            ]);
            
            // Try different time formats (including 12-hour format with AM/PM)
            $formats = ['H:i:s', 'H:i', 'g:i A', 'g:i:s A', 'g:iA', 'g:i:sA'];
            $timeObj = null;
            
            foreach ($formats as $format) {
                try {
                    $timeObj = \Carbon\Carbon::createFromFormat($format, $timeStr);
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (!$timeObj) {
                // If no format works, try to parse as a general time
                $timeObj = \Carbon\Carbon::parse($timeStr);
            }

            // Convert JST to PHT (subtract 1 hour)
            $phtTime = $timeObj->subHour();
            $result = $phtTime->format('H:i');
            
            // Debug: Log the conversion result
            Log::debug('JST to PHT conversion result', [
                'original' => $timeStr,
                'converted' => $result
            ]);
            
            return $result;
        } catch (\Exception $e) {
            // If conversion fails, return the original time as string
            return (string)$time;
        }
    }
}
