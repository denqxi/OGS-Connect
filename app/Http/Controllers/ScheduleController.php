<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\DailyData;
use App\Models\TutorAvailability;
use App\Models\TutorAssignment;
use App\Services\TutorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            // Get available dates for filter dropdown
            $availableDates = DailyData::select('date')
                ->distinct()
                ->orderBy('date')
                ->pluck('date');

            // Check if viewing a specific date
            if ($request->filled('view_date')) {
                return $this->showPerDaySchedule($request->view_date);
            }

            // Group by date for table view - Build query with proper filtering
            $query = DailyData::query();
            
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
                 )) as total_assigned';
            
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
                               $tutor->full_name = $tutor->full_name; // Use the accessor
                               return $tutor;
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
            $classId = $request->input('class_id');
            $tutorNames = $request->input('tutors', []);
            
            // Find the class
            $class = DailyData::findOrFail($classId);
            
            // Remove existing assignments for this class
            TutorAssignment::where('daily_data_id', $classId)->delete();
            
            // Add new assignments
            $assignedCount = 0;
            foreach ($tutorNames as $tutorName) {
                if (trim($tutorName) !== '') {
                    $tutor = Tutor::where('tusername', $tutorName)->first();
                    if ($tutor) {
                        TutorAssignment::create([
                            'daily_data_id' => $classId,
                            'tutor_id' => $tutor->tutorID,
                            'assigned_at' => now(),
                        ]);
                        $assignedCount++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully saved {$assignedCount} tutor assignments for {$class->class}",
                'assigned_count' => $assignedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving tutor assignments: ' . $e->getMessage());
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
                 )) as total_assigned';
            
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
}