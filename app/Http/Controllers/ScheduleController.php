<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\DailyData;
use App\Models\TutorAvailability;
use App\Models\TutorAssignment;
use App\Models\ScheduleHistory;
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
use App\Models\Supervisor;

class ScheduleController extends Controller
{
    protected $assignmentService;

    public function __construct(TutorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'employee');

        if ($tab === 'class') {
            // Get available dates for filter dropdown (exclude finalized schedules)
            $availableDates = DailyData::select('date')
                ->where(function($q) {
                    $q->where('schedule_status', '!=', 'finalized')
                      ->orWhereNull('schedule_status');
                })
                ->distinct()
                ->orderBy('date')
                ->pluck('date');

            // Check if viewing a specific date
            if ($request->filled('view_date')) {
                return $this->showPerDaySchedule($request->view_date, $request->get('page', 1));
            }

            // Group by date for table view - Build query with proper filtering
            $query = DailyData::query();
            
            // Exclude finalized schedules from main class scheduling view
                        $query->where(function($q) {
                                $q->where('schedule_status', '!=', 'finalized')
                                    ->orWhereNull('schedule_status');
                        });
            
            // Apply filters first
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
            
            // Now build the grouped query with accurate totals
            $selectRaw = 'date, day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
                SUM(CASE WHEN class_status = \'active\' THEN 1 ELSE 0 END) as active_class_count,
                SUM(CASE WHEN class_status = \'cancelled\' THEN 1 ELSE 0 END) as cancelled_class_count,
                SUM(CASE WHEN class_status = \'active\' THEN number_required ELSE 0 END) as total_required,
                (SELECT COUNT(*) FROM tutor_assignments ta 
                 WHERE ta.daily_data_id IN (
                     SELECT dd2.id FROM daily_data dd2 
                     WHERE dd2.date = daily_data.date AND dd2.day = daily_data.day AND dd2.class_status = \'active\''
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

            return view('schedules.index', compact('dailyData', 'availableDates'));

        } elseif ($tab === 'history') {
            // Schedule History - show finalized schedules
            return $this->showScheduleHistory($request);
            
        } elseif ($tab === 'employee') {
            // Employee availability logic - Filter by GLS account only
            $query = Tutor::with(['accounts' => function($query) {
                $query->forAccount('GLS')->active();
            }])
            ->whereHas('accounts', function($query) {
                $query->forAccount('GLS')->active();
            });
            
            // Apply search filter
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
            
            // Apply status filter (for tutor status)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Apply time range filter (for GLS account availability)
            if ($request->filled('time_range')) {
                $query->whereHas('accounts', function($q) use ($request) {
                    $q->forAccount('GLS')->active();
                    
                    // Use a more flexible approach to filter by time ranges
                    switch($request->time_range) {
                        case 'morning': // 6AM-12PM
                            $q->where(function($timeQuery) {
                                // Check if any available times fall within morning hours
                                $timeQuery->where('available_times', 'like', '%06:00%')
                                    ->orWhere('available_times', 'like', '%07:00%')
                                    ->orWhere('available_times', 'like', '%08:00%')
                                    ->orWhere('available_times', 'like', '%09:00%')
                                    ->orWhere('available_times', 'like', '%10:00%')
                                    ->orWhere('available_times', 'like', '%11:00%')
                                    ->orWhere('preferred_time_range', 'morning');
                            });
                            break;
                        case 'afternoon': // 12PM-6PM  
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
                        case 'evening': // 6PM-12AM
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
            
            // Apply day filter (for GLS account availability)
            if ($request->filled('day')) {
                $dayName = ucfirst($request->day); // Convert 'monday' to 'Monday'
                $query->whereHas('accounts', function($q) use ($dayName) {
                    $q->forAccount('GLS')->active()
                      ->whereJsonContains('available_days', $dayName);
                });
            }
            
            $tutors = $query->paginate(5)->withQueryString();
            return view('schedules.index', compact('tutors'));
        }

        // Default case - redirect to employee tab
        return redirect()->route('schedules.index', ['tab' => 'employee']);
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
        
        // Check if this schedule is finalized
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
        // If viewing a specific date, return that view
        if ($request->has('view_date')) {
            // Validate that the date exists and is finalized
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

        // Query for finalized schedules grouped by date
        $query = DailyData::select([
            'date',
            'day',
            DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
            DB::raw('COUNT(*) as class_count'),
            DB::raw('SUM(number_required) as total_required'),
            DB::raw('(SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned'),
            'schedule_status',
            'finalized_at'
        ])
    ->where('schedule_status', 'finalized')
        ->groupBy('date', 'day', 'schedule_status', 'finalized_at');

        // Apply filters
        if ($request->filled('search')) {
            $query->having('schools', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        // Get finalized schedules ordered by date (newest first) with pagination
        $scheduleHistory = $query->orderBy('date', 'desc')
            ->paginate(5)
            ->withQueryString();

        // Get available dates and days for filters
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
                $conflictInfo = $conflicts[$tutorUsername][0]; // Get first conflict
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
            $tutorName = $assignment->tutor->tusername;
            $className = $assignment->dailyData->class;
            $wasMainTutor = !$assignment->is_backup;
            $dailyDataId = $assignment->daily_data_id;
            
            // Delete the assignment
            $assignment->delete();
            
            $message = "Removed {$tutorName} from {$className}";
            
            // Auto-promotion logic: If a main tutor was removed, promote backup tutor
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
            // Promote backup to main tutor and mark as promoted
            $backupTutor->update([
                'is_backup' => false,
                'was_promoted_from_backup' => true,
                'replaced_tutor_name' => $removedTutorName,
                'promoted_at' => now()
            ]);
            
            $backupTutorName = $backupTutor->tutor->tusername;
            
            // Create history record for the promotion
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
                                   'username' => $tutor->tusername, // Map tusername to username for JavaScript
                                   'email' => $tutor->email,
                                   'first_name' => $tutor->first_name,
                                   'last_name' => $tutor->last_name,
                                   'full_name' => $tutor->full_name // Use the accessor
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
        
        // Add backup tutor to check list if provided
        if ($backupTutorName) {
            $allTutorNames[] = $backupTutorName;
        }
        
        foreach ($allTutorNames as $tutorName) {
            if (empty(trim($tutorName))) continue;
            
            $tutor = Tutor::where('tusername', $tutorName)->first();
            if (!$tutor) continue;
            
            // Find other assignments for this tutor on the same date and time
            $conflictingAssignments = TutorAssignment::where('tutor_id', $tutor->tutorID)
                ->where('daily_data_id', '!=', $classId) // Exclude current class
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
            
            // Find the class
            $class = DailyData::findOrFail($classId);
            
            // Check for time conflicts before proceeding
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
            
            // Get current assignments for this class
            $existingMainTutors = TutorAssignment::where('daily_data_id', $classId)
                                                ->where('is_backup', false)
                                                ->get();
            $existingBackupTutors = TutorAssignment::where('daily_data_id', $classId)
                                                  ->where('is_backup', true)
                                                  ->get();
            
            // If tutors array is empty, preserve existing main tutor assignments
            if (empty($tutorNames)) {
                Log::info('No tutors in request - preserving existing main assignments', [
                    'existing_main_count' => $existingMainTutors->count()
                ]);
                
                $assignedCount = $existingMainTutors->count();
            } else {
                // Normal case: replace main tutor assignments with provided tutors
                Log::info('Replacing main assignments with provided tutors', [
                    'tutor_count' => count($tutorNames)
                ]);
                
                // Remove existing MAIN assignments for this class (preserve backup)
                TutorAssignment::where('daily_data_id', $classId)
                              ->where('is_backup', false)
                              ->delete();
                
                // Add new main tutor assignments
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
            
            // Handle backup tutor separately
            if ($backupTutor && isset($backupTutor['username']) && trim($backupTutor['username']) !== '') {
                $backupTutorModel = Tutor::where('tusername', $backupTutor['username'])->first();
                
                if ($backupTutorModel) {
                    // Remove existing backup tutors for this class (only allow one backup)
                    TutorAssignment::where('daily_data_id', $classId)
                                  ->where('is_backup', true)
                                  ->delete();
                    
                    // Check if this tutor is already assigned as a main tutor
                    $alreadyAssignedAsMain = TutorAssignment::where('daily_data_id', $classId)
                                                          ->where('tutor_id', $backupTutorModel->tutorID)
                                                          ->where('is_backup', false)
                                                          ->exists();
                    
                    if (!$alreadyAssignedAsMain) {
                        // Add as backup tutor
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
            
            // Get final counts
            $finalMainCount = TutorAssignment::where('daily_data_id', $classId)
                                            ->where('is_backup', false)
                                            ->count();
            $finalBackupCount = TutorAssignment::where('daily_data_id', $classId)
                                              ->where('is_backup', true)
                                              ->count();
            
            // (Removed auto-promotion of backup tutors to main slots. Backup tutors will only be promoted by explicit user action in the UI.)
            
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
            
            // Build query with filters
            $query = DailyData::query();
            
            // Exclude finalized schedules from search results
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
            
            // Build grouped query with assignment counts
            $selectRaw = 'date, day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
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
            
            // Return HTML partial for AJAX
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
            
            // Do NOT auto-promote backup tutors. Promotion should only occur by explicit user action in the UI.
            // $this->checkAndAutoPromoteBackupTutors($classId);
            
            // Get main tutors (is_backup = false or null)
            $mainTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where(function($query) {
                    $query->where('is_backup', false)
                          ->orWhereNull('is_backup');
                })
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    // For UI display, always show the actual tutor name
                    // The replacement message is only for Excel exports
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_promoted' => $assignment->was_promoted_from_backup,
                        'replaced_tutor' => $assignment->replaced_tutor_name
                    ];
                });

            // Get backup tutors (is_backup = true)
            $backupTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('is_backup', true)
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    // For UI display, always show the actual tutor name
                    // The replacement message is only for Excel exports
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
            $updated = DailyData::where('date', $date)
                ->update([
                    'schedule_status' => 'finalized',
                    'finalized_at' => now()
                ]);

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

    /**
     * Export tentative schedule to Excel (Class Scheduling tab)
     */
    public function exportTentativeSchedule(Request $request)
    {
        try {
            // Build the query for non-finalized schedules
            $query = DailyData::with(['tutorAssignments.tutor'])
                                ->where(function($q) {
                                        $q->where('schedule_status', '!=', 'finalized')
                                            ->orWhereNull('schedule_status');
                                });
            
            // If specific date is provided, filter by that date
            if ($request->has('date') && $request->date) {
                $query->where('date', $request->date);
            }
            
            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json(['error' => 'No tentative schedules found for the specified criteria'], 404);
            }

            return $this->generateExcel($schedules, 'Tentative Schedule');
        } catch (\Exception $e) {
            Log::error('Error exporting tentative schedule: ' . $e->getMessage());
            return back()->with('error', 'Failed to export: ' . $e->getMessage());
        }
    }

    /**
     * Export selected schedules to Excel (Schedule History tab)
     */
    public function exportSelectedSchedules(Request $request)
    {
        try {
            $request->validate([
                'dates' => 'required|array|min:1',
                'dates.*' => 'required|date'
            ]);

            $selectedDates = $request->input('dates');
            
            // Get schedule data for selected dates (remove final status requirement for Final Excel button)
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

            return $this->generateExcel($schedules, 'selected');
            
        } catch (\Exception $e) {
            Log::error('Error exporting selected schedules: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed'], 500);
        }
    }

    /**
     * Export finalized schedule to Excel (Schedule History tab)
     */
    public function exportFinalSchedule(Request $request)
    {
        try {
            // Build the query for finalized schedules
            $query = DailyData::with(['tutorAssignments.tutor'])
                ->where('schedule_status', 'finalized');
            
            // If specific date is provided, filter by that date
            if ($request->has('date') && $request->date) {
                $query->where('date', $request->date);
            }
            
            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json(['error' => 'No finalized schedules found for the specified criteria'], 404);
            }

            return $this->generateExcel($schedules, 'Finalized Schedule');
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title === 'Finalized Schedule' ? 'Finalized Schedule' : 'Tentative Schedule');

        // Check if this is a finalized schedule export (different format)
        if ($title === 'Finalized Schedule' || $title === 'selected') {
            return $this->generateFinalizedScheduleExcel($spreadsheet, $schedules, $title);
        }

        // Original tentative schedule format (column-based)
        return $this->generateTentativeScheduleExcel($spreadsheet, $schedules, $title);
    }

    /**
     * Generate finalized schedule Excel with detailed tutor list format
     */
    private function generateFinalizedScheduleExcel($spreadsheet, $schedules, $title)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Overview');
        
        // Group schedules by date and class, and prepare individual class sheet data
        $groupedSchedules = [];
        $classSheetsData = []; // Reverted back to individual class sheets
        
        foreach ($schedules as $schedule) {
            $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y'); // Full date format
            $time = '';
            
            if ($schedule->time_jst) {
                // Convert JST to PHT (JST - 1 hour)
                $phtTime = \Carbon\Carbon::parse($schedule->time_jst)->subHour();
                $time = $phtTime->format('g:i A'); // 12-hour format with AM/PM
            }
            
            $key = $date . ' - ' . $time . ' (' . $schedule->school . ')';
            
            // Create sheet key based on date, school, and class name (for individual class sheets)
            $shortDate = \Carbon\Carbon::parse($schedule->date)->format('M. d, Y'); // Sept. 02, 2025
            $sheetKey = $shortDate . ' - ' . $schedule->school . ' - ' . $schedule->class; // Sept. 02, 2025 - Takada - Math
            
            // Add "CANCELLED" to sheet name if class is cancelled
            if ($schedule->class_status === 'cancelled') {
                $sheetKey .= ' - CANCELLED';
            }
            
            if (!isset($classSheetsData[$sheetKey])) {
                $classSheetsData[$sheetKey] = [];
            }
            if (!isset($groupedSchedules[$key])) {
                $groupedSchedules[$key] = [
                    'schedules' => [],
                    'date' => $date,
                    'time' => $time,
                    'school' => $schedule->school,
                    'tutors' => []
                ];
            }
            
            $groupedSchedules[$key]['schedules'][] = $schedule;
            
            // Collect all tutors (main and backup) with full details for this class sheet
            foreach ($schedule->tutorAssignments as $assignment) {
                $tutor = $assignment->tutor;
                
                // Get GLS account info for this tutor including username and screen name
                $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                
                // Use GLS account credentials if available, otherwise use tutor's basic info
                $glsId = $glsAccount && $glsAccount->gls_id ? $glsAccount->gls_id : $tutor->tutorID;
                $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : 'N/A';
                $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : 'N/A';
                
                // Determine the display name based on promotion status
                if ($assignment->was_promoted_from_backup) {
                    if ($assignment->replaced_tutor_name === 'Auto-filled vacant slot') {
                        $displayName = "{$tutor->full_name} (promoted from backup)";
                    } else {
                        $displayName = "{$tutor->full_name} (replaced {$assignment->replaced_tutor_name})";
                    }
                } else {
                    $displayName = $tutor->full_name;
                }
                
                $tutorData = [
                    'tutorID' => $tutor->tutorID,
                    'full_name' => $displayName,
                    'username' => $tutor->tusername,
                    'screen_name' => $tutor->tusername, // Use username as screen name since screen_name field doesn't exist
                    'sex' => $tutor->sex ?? 'N/A', // Fetch sex from tutors table
                    'is_backup' => $assignment->is_backup,
                    'class_name' => $schedule->class, // Add class name for reference
                    'time' => $time,
                    'is_cancelled' => $schedule->class_status === 'cancelled' // Add cancellation status
                ];
                
                $groupedSchedules[$key]['tutors'][] = $tutorData;
                
                // Add ALL tutors (main and backup) to the individual class sheet
                $classSheetsData[$sheetKey][] = [
                    'glsID' => $glsId,
                    'full_name' => $displayName, // Use the same display name logic
                    'glsUsername' => $glsUsername,  // Use GLS account username
                    'glsScreenName' => $glsScreenName,  // Use GLS account screen name
                    'sex' => $tutor->sex ?? 'N/A', // Fetch from tutors table
                    'supervisor' => 'N/A', // Will be updated when supervisor/logging functionality is integrated
                    'is_backup' => $assignment->is_backup,
                    'has_gls_account' => $glsAccount ? true : false,
                    'class_name' => $schedule->class,
                    'time' => $time,
                    'is_cancelled' => $schedule->class_status === 'cancelled' // Add cancellation status
                ];
            }
            
            // If class is cancelled and has no tutors, still create an entry to show in export
            if ($schedule->class_status === 'cancelled' && $schedule->tutorAssignments->count() === 0) {
                $classSheetsData[$sheetKey][] = [
                    'glsID' => 'N/A',
                    'full_name' => 'CLASS CANCELLED',
                    'glsUsername' => 'N/A',
                    'glsScreenName' => 'N/A',
                    'sex' => 'N/A',
                    'supervisor' => 'N/A',
                    'is_backup' => false,
                    'has_gls_account' => false,
                    'class_name' => $schedule->class,
                    'time' => $time,
                    'is_cancelled' => true
                ];
            }
        }

        // For finalized schedules, remove the default sheet and only create individual class sheets
        $spreadsheet->removeSheetByIndex(0);
        
        // Create individual class sheets (editable) with date, school, and class names
        $this->createClassSheets($spreadsheet, $classSheetsData, true, true); // true for finalized = show cancelled markings

        // Generate and output file (without protection for finalized schedules)
        $this->finalizeAndOutputEditableExcel($spreadsheet, $title);
    }

    /**
     * Generate tentative schedule Excel with column-based format plus individual class sheets
     */
    private function generateTentativeScheduleExcel($spreadsheet, $schedules, $title)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Overview');

        // Group schedules by date and time, including school info
        $groupedSchedules = [];
        $classSheetsData = [];
        
        foreach ($schedules as $schedule) {
            $date = \Carbon\Carbon::parse($schedule->date)->format('M j');
            $time = '';
            
            if ($schedule->time_jst) {
                // Convert JST to PHT (JST - 1 hour)
                $phtTime = \Carbon\Carbon::parse($schedule->time_jst)->subHour();
                $time = $phtTime->format('G:i') . ($phtTime->format('A') === 'AM' ? 'am' : 'pm');
            }
            
            $key = $date . ' (' . $time . ')';
            if (!isset($groupedSchedules[$key])) {
                $groupedSchedules[$key] = [
                    'schedules' => [],
                    'total_slots' => 0,
                    'main_tutors' => [],
                    'backup_tutors' => [],
                    'schools' => []
                ];
            }
            
            $groupedSchedules[$key]['schedules'][] = $schedule;
            $groupedSchedules[$key]['total_slots'] += $schedule->number_required ?? 0;
            
            // Collect school names
            if (!in_array($schedule->school, $groupedSchedules[$key]['schools'])) {
                $groupedSchedules[$key]['schools'][] = $schedule->school;
            }
            
            // Get tutors for this schedule and prepare class sheet data
            // Create sheet key in format "M. d, Y - School - Class"
            $shortDate = \Carbon\Carbon::parse($schedule->date)->format('M. d, Y'); // Sept. 02, 2025
            $className = $shortDate . ' - ' . $schedule->school . ' - ' . $schedule->class;
            
            // For tentative schedules, don't mark cancelled classes differently
            
            if (!isset($classSheetsData[$className])) {
                $classSheetsData[$className] = [];
            }
            
            foreach ($schedule->tutorAssignments as $assignment) {
                $tutor = $assignment->tutor;
                
                // Get GLS account info for this tutor including username and screen name
                $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                
                // Use GLS account credentials if available, otherwise use tutor's basic info
                $glsId = $glsAccount && $glsAccount->gls_id ? $glsAccount->gls_id : $tutor->tutorID;
                $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : 'N/A';
                $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : 'N/A';
                
                // Determine the display name based on promotion status
                if ($assignment->was_promoted_from_backup) {
                    if ($assignment->replaced_tutor_name === 'Auto-filled vacant slot') {
                        $displayName = "{$tutor->full_name} (promoted from backup)";
                    } else {
                        $displayName = "{$tutor->full_name} (replaced {$assignment->replaced_tutor_name})";
                    }
                } else {
                    $displayName = $tutor->full_name;
                }
                
                if ($assignment->is_backup) {
                    if (!in_array($displayName, $groupedSchedules[$key]['backup_tutors'])) {
                        $groupedSchedules[$key]['backup_tutors'][] = $displayName;
                    }
                } else {
                    $groupedSchedules[$key]['main_tutors'][] = $displayName;
                }
                
                // Add ALL tutors (main and backup) to the individual class sheet
                $classSheetsData[$className][] = [
                    'glsID' => $glsId,
                    'full_name' => $displayName, // Use the display name logic
                    'glsUsername' => $glsUsername,  // Use GLS account username
                    'glsScreenName' => $glsScreenName,  // Use GLS account screen name
                    'sex' => $tutor->sex ?? 'N/A', // Fetch from tutors table
                    'supervisor' => 'N/A', // Placeholder for supervisor information
                    'is_backup' => $assignment->is_backup,
                    'has_gls_account' => $glsAccount ? true : false,
                    'class_name' => $schedule->class,
                    'time' => $time,
                    'is_cancelled' => false // For tentative exports, don't mark as cancelled
                ];
            }
            
            // For tentative exports, skip cancelled classes with no tutors (don't show them at all)
            // Note: Cancelled classes with tutors are already included above but not marked as cancelled
        }

        // Create overview sheet (existing format)
        $this->createOverviewSheet($sheet, $groupedSchedules);
        
        // Create individual class sheets (editable)
        $this->createClassSheets($spreadsheet, $classSheetsData, true, false); // false for tentative = no cancelled markings

        // Generate and output editable file
        $this->finalizeAndOutputEditableExcel($spreadsheet, $title);
    }

    /**
     * Create the overview sheet with the existing format
     */
    private function createOverviewSheet($sheet, $groupedSchedules)
    {
        $columnIndex = 1; // Start from column A (1)
        $columnLetters = [];
        
        foreach ($groupedSchedules as $timeSlot => $data) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $columnLetters[] = $columnLetter;
            
            // Set school info first (row 1)
            $schoolText = implode(', ', $data['schools']);
            $sheet->setCellValue($columnLetter . '1', $schoolText);
            
            // Set time slot header (row 2)
            $sheet->setCellValue($columnLetter . '2', $timeSlot);
            
            // Set slot count (row 3)
            $slotText = '(' . $data['total_slots'] . ' Slots)';
            $sheet->setCellValue($columnLetter . '3', $slotText);
            
            // Style the school row (row 1)
            $sheet->getStyle($columnLetter . '1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFACD']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            // Style the time slot headers (row 2)
            $sheet->getStyle($columnLetter . '2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'ADD8E6']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            // Style the slot count (row 3)
            $sheet->getStyle($columnLetter . '3')->applyFromArray([
                'font' => ['italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            $columnIndex++;
        }

        // Find the maximum number of tutors needed for proper row spacing
        $maxTutors = 0;
        $maxBackupTutors = 0;
        
        foreach ($groupedSchedules as $data) {
            $maxTutors = max($maxTutors, count($data['main_tutors']));
            $maxBackupTutors = max($maxBackupTutors, count($data['backup_tutors']));
        }

        // Fill in the tutor data
        $row = 4; // Start from row 4 (after school, time, and slot count rows)
        $columnIndex = 1;
        
        foreach ($groupedSchedules as $timeSlot => $data) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            
            // Add main tutors
            $currentRow = $row;
            foreach ($data['main_tutors'] as $tutor) {
                $sheet->setCellValue($columnLetter . $currentRow, $tutor);
                $sheet->getStyle($columnLetter . $currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $currentRow++;
            }
            
            // Add "BACK UP" header
            $backupStartRow = $row + $maxTutors + 1;
            $sheet->setCellValue($columnLetter . $backupStartRow, 'BACK UP');
            $sheet->getStyle($columnLetter . $backupStartRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFEB9C']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            // Add backup tutors
            $currentRow = $backupStartRow + 1;
            foreach ($data['backup_tutors'] as $backupTutor) {
                $sheet->setCellValue($columnLetter . $currentRow, $backupTutor);
                $sheet->getStyle($columnLetter . $currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6CC']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $currentRow++;
            }
            
            $columnIndex++;
        }

        // Auto-size all columns
        foreach ($columnLetters as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }
        
        // Set minimum column width
        foreach ($columnLetters as $letter) {
            if ($sheet->getColumnDimension($letter)->getWidth() < 15) {
                $sheet->getColumnDimension($letter)->setWidth(15);
            }
        }
    }

    /**
     * Create individual class sheets with GLS tutor information
     */
    private function createClassSheets($spreadsheet, $classSheetsData, $editable = false, $showCancelledMarkings = true)
    {
        foreach ($classSheetsData as $className => $tutorData) {
            $classSheet = $spreadsheet->createSheet();
            $classSheet->setTitle($this->sanitizeSheetName($className));
            
            // Check if this is a cancelled class
            $isCancelled = !empty($tutorData) && ($tutorData[0]['is_cancelled'] ?? false) && $showCancelledMarkings;
            
            // Set headers
            $classSheet->setCellValue('A1', 'No.');
            $classSheet->setCellValue('B1', 'glsID');
            $classSheet->setCellValue('C1', 'Full Name');
            $classSheet->setCellValue('D1', 'glsUsername');
            $classSheet->setCellValue('E1', 'glsScreenName');
            $classSheet->setCellValue('F1', 'Sex');
            $classSheet->setCellValue('G1', 'Supervisor');
            
            // Style headers with different color for cancelled classes (only if showing cancelled markings)
            $headerColor = $isCancelled ? 'FF9999' : 'ADD8E6'; // Red for cancelled, blue for normal
            $classSheet->getStyle('A1:G1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $headerColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            // Add cancellation notice if class is cancelled
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
                $row += 2; // Skip a row after the notice
                
                // If there are no actual tutors (just placeholder), don't show tutor data
                if (count($tutorData) === 1 && $tutorData[0]['full_name'] === 'CLASS CANCELLED') {
                    // Only show the cancellation notice
                    $classSheet->getColumnDimension('A')->setAutoSize(true);
                    continue;
                }
            } else {
                $row = 2;
            }
            
            // Separate main tutors and backup tutors
            $mainTutors = [];
            $backupTutors = [];
            
            foreach ($tutorData as $tutor) {
                if ($tutor['is_backup']) {
                    $backupTutors[] = $tutor;
                } else {
                    $mainTutors[] = $tutor;
                }
            }
            
            // Fill main tutor data first
            $number = 1;
            foreach ($mainTutors as $tutor) {
                $classSheet->setCellValue('A' . $row, $number);
                $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                $classSheet->setCellValue('F' . $row, $tutor['sex']);
                $classSheet->setCellValue('G' . $row, $tutor['supervisor'] ?? 'N/A');
                
                // Style the row with grayed out effect for cancelled classes (only if showing cancelled markings)
                $backgroundColor = ($isCancelled && $showCancelledMarkings) ? 'F0F0F0' : 'FFFFFF'; // Gray for cancelled, white for normal
                $fontColor = ($isCancelled && $showCancelledMarkings) ? '808080' : '000000'; // Gray text for cancelled, black for normal
                
                $classSheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backgroundColor]],
                    'font' => ['color' => ['rgb' => $fontColor]]
                ]);
                
                $row++;
                $number++;
            }
            
            // Add backup tutors section if there are any
            if (count($backupTutors) > 0) {
                // Add a separator row
                $row++;
                $classSheet->setCellValue('A' . $row, 'BACKUP TUTORS');
                $classSheet->mergeCells('A' . $row . ':G' . $row);
                
                $backupHeaderColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFF2CC'; // Darker gray for cancelled, yellow for normal
                $backupFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000'; // Gray text for cancelled, black for normal
                
                $classSheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $backupFontColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupHeaderColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $row++;
                
                // Fill backup tutor data
                foreach ($backupTutors as $tutor) {
                    $classSheet->setCellValue('A' . $row, $number);
                    $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                    $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                    $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                    $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                    $classSheet->setCellValue('F' . $row, $tutor['sex']);
                    $classSheet->setCellValue('G' . $row, $tutor['supervisor'] ?? 'N/A');
                    
                    // Style the row with backup highlighting and cancelled graying
                    $backupRowColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFE6CC'; // Gray for cancelled, orange for normal
                    $backupRowFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000'; // Gray text for cancelled, black for normal
                    
                    $classSheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupRowColor]],
                        'font' => ['color' => ['rgb' => $backupRowFontColor]]
                    ]);
                    
                    $row++;
                    $number++;
                }
            }
            
            // Auto-size columns
            foreach (range('A', 'G') as $column) {
                $classSheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Only protect sheet if not editable
            if (!$editable) {
                $classSheet->getProtection()->setSheet(true);
            }
        }
    }

    /**
     * Sanitize sheet name for Excel compatibility
     */
    private function sanitizeSheetName($name)
    {
        // Excel sheet names can't be longer than 31 characters and can't contain certain characters
        $name = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '-', $name);
        return substr($name, 0, 31);
    }

    /**
     * Finalize and output the Excel file (editable version for both tentative and finalized schedules)
     */
    private function finalizeAndOutputEditableExcel($spreadsheet, $title)
    {
        // Set the first available sheet as active
        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

        // Generate filename
        $filename = str_replace(' ', '_', $title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and output file (no protection - editable)
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Finalize and output the Excel file (protected version for tentative schedules)
     */
    private function finalizeAndOutputExcel($spreadsheet, $title)
    {
        // Set the active sheet back to the first sheet (Overview)
        $spreadsheet->setActiveSheetIndex(0);
        
        // Protect all sheets to make them read-only
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setSelectLockedCells(true);
            $sheet->getProtection()->setSelectUnlockedCells(true);
        }

        // Generate filename
        $filename = str_replace(' ', '_', $title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and output file
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
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
            
            // Start transaction
            DB::beginTransaction();
            
            // Don't remove tutor assignments - just mark class as cancelled
            // This preserves assignments so they can be restored later
            
            // Mark class as cancelled
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
            $status = $request->input('status'); // 'tentative' or 'final'

            // Get the first supervisor's supID (or null if none)
            $supervisor = \App\Models\Supervisor::first();
            $performedBy = $supervisor ? $supervisor->supID : null;

            // Validate inputs
            if (!$date || !in_array($status, ['tentative', 'final'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date or status provided'
                ], 400);
            }

            // Get all classes for the date (include both active and cancelled)
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
                // Store old data for history
                $oldData = [
                    'schedule_status' => $class->schedule_status,
                    'finalized_at' => $class->finalized_at,
                    'finalized_by' => $class->finalized_by
                ];

                // Update schedule status
                $updateData = ['schedule_status' => $scheduleStatus];
                if ($status === 'final') {
                    $updateData['finalized_at'] = now();
                    $updateData['finalized_by'] = $performedBy;
                }

                $class->update($updateData);

                // Always create a history record for both active and cancelled classes
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

        // Apply filters
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
        $users = \App\Models\User::orderBy('name')->get();

        return view('schedules.history', compact('histories', 'users'));
    }

    /**
     * Export schedule history as CSV
     */
    public function exportHistory(Request $request)
    {
        $query = ScheduleHistory::with(['dailyData', 'performer'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as history view
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

        // Create CSV content
        $filename = 'schedule_history_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($histories) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
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

            // Data rows
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
}