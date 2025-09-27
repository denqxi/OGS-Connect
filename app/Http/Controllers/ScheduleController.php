<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\DailyData;
use App\Models\TutorAvailability;
use App\Models\TutorAssignment;
use App\Services\TutorAssignmentService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    $q->where('schedule_status', '!=', 'final')
                      ->orWhereNull('schedule_status');
                })
                ->distinct()
                ->orderBy('date')
                ->pluck('date');

            // Check if viewing a specific date
            if ($request->filled('view_date')) {
                return $this->showPerDaySchedule($request->view_date);
            }

            // Group by date for table view - Build query with proper filtering
            $query = DailyData::query();
            
            // Exclude finalized schedules from main class scheduling view
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'final')
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
        } elseif ($status === 'partial') {
            $query->whereHas('tutorAssignments', function($q) {
                $q->havingRaw('COUNT(*) < number_required AND COUNT(*) > 0');
            });
        } elseif ($status === 'unassigned') {
            $query->whereDoesntHave('tutorAssignments');
        }
    }

    private function showPerDaySchedule($date)
    {
        $dailyData = DailyData::where('date', $date)->with(['tutorAssignments.tutor'])->get();
        return view('schedules.index', compact('dailyData', 'date'));
    }

    /**
     * Show schedule history (finalized schedules)
     */
    private function showScheduleHistory(Request $request)
    {
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
        ->where('schedule_status', 'final')
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

        // Get finalized schedules ordered by date (newest first)
        $scheduleHistory = $query->orderBy('date', 'desc')->get();

        // Get available dates and days for filters
        $availableDates = DailyData::where('schedule_status', 'final')
            ->select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        $availableDays = DailyData::where('schedule_status', 'final')
            ->select('day')
            ->distinct()
            ->pluck('day');

        return view('schedules.index', compact('scheduleHistory', 'availableDates', 'availableDays'));
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
            
            $assignment->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Removed {$tutorName} from {$className}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove assignment: ' . $e->getMessage()
            ], 500);
        }
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
            
            return response()->json([
                'success' => true,
                'html' => view('schedules.partials.class-table-rows', compact('dailyData'))->render(),
                'pagination' => $dailyData->links()->render(),
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
     * Save schedule as partial for a specific date
     */
    public function saveAsPartial(Request $request, $date)
    {
        try {
            $updated = DailyData::where('date', $date)
                ->update([
                    'schedule_status' => 'partial'
                ]);

            return response()->json([
                'success' => true,
                'message' => "Schedule for {$date} saved as partial ({$updated} classes updated)."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save as partial: ' . $e->getMessage()
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
            
            // Get main tutors (is_backup = false or null)
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
                        'full_name' => $assignment->tutor->full_name
                    ];
                });

            // Get backup tutors (is_backup = true)
            $backupTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('is_backup', true)
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name
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
                    'schedule_status' => 'final',
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
                    $q->where('schedule_status', '!=', 'final')
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
            
            // Get schedule data for selected dates
            $schedules = DailyData::with(['tutorAssignments.tutor'])
                ->whereIn('date', $selectedDates)
                ->where('schedule_status', 'final')
                ->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json(['error' => 'No schedules found for selected dates'], 404);
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
                ->where('schedule_status', 'final');
            
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
        $sheet->setTitle('Tentative Schedule');

        // Group schedules by date and time, including school info
        $groupedSchedules = [];
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
            
            // Get tutors for this schedule
            foreach ($schedule->tutorAssignments as $assignment) {
                if ($assignment->is_backup) {
                    if (!in_array($assignment->tutor->full_name, $groupedSchedules[$key]['backup_tutors'])) {
                        $groupedSchedules[$key]['backup_tutors'][] = $assignment->tutor->full_name;
                    }
                } else {
                    $groupedSchedules[$key]['main_tutors'][] = $assignment->tutor->full_name;
                }
            }
        }

        // Set up column headers (time slots)
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

        // Protect the sheet to make it read-only
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setSelectLockedCells(true);
        $sheet->getProtection()->setSelectUnlockedCells(true);

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
}