<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\DailyData;
use App\Models\ScheduleHistory;
use App\Models\Supervisor;
use App\Models\AuditLog;
use App\Http\Requests\SaveScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Main index method - handles all tabs and views
     */
    public function index(Request $request)
    {
        try {
            $tab = $request->input('tab', 'employee');

        // Class scheduling tab
        if ($tab === 'class') {
            // Only show per-day view if view_date is specifically requested (from View button)
            if ($request->filled('view_date')) {
                $date = $request->input('view_date');
                return $this->showPerDayScheduleData($date, $request->input('page', 1), $request);
            }

            // Check if there are any filter parameters (non-empty)
            if ($request->filled('search') || $request->filled('day') || $request->filled('status') || $request->filled('date')) {
                return $this->searchSchedules($request);
            }

            return $this->showClassScheduleList($request);
        }

            // Schedule History tab
            if ($tab === 'history') {
                return $this->showScheduleHistoryData($request);
            }

            // Employee availability tab
            if ($tab === 'employee') {
                return $this->showEmployeeAvailability($request);
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
     * Show class schedule list with filtering
     */
    private function showClassScheduleList(Request $request)
    {
            $query = DailyData::query();
            
        // Only show non-finalized schedules in class-scheduling tab
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'finalized')
                  ->orWhereNull('schedule_status');
            });

        // Apply filters
            if ($request->filled('search')) {
                $query->where('school', 'like', '%' . $request->search . '%');
            }

            // Prioritize date filter over day filter - only apply one at a time
            $dateFilter = $request->input('date');
            $dayFilter = $request->input('day');
            
            if ($dateFilter && trim($dateFilter) !== '') {
                // Try both whereDate and direct comparison
                $query->where(function($q) use ($dateFilter) {
                    $q->whereDate('date', $dateFilter)
                      ->orWhere('date', $dateFilter);
                });
            } elseif ($dayFilter && trim($dayFilter) !== '') {
                $day = $this->validateDayName($dayFilter);
                if ($day) {
                    // Use DAYOFWEEK instead of DAYNAME for more reliable filtering
                    $dayOfWeek = $this->getDayOfWeek($day);
                    if ($dayOfWeek) {
                        $query->whereRaw('DAYOFWEEK(date) = ?', [$dayOfWeek]);
                    }
                }
            }

            $dailyData = $query->selectRaw('date, DAYNAME(date) as day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 0 ELSE 1 END) as active_class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_class_count,
                SUM(number_required) as total_required,
                (SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned,
                GROUP_CONCAT(DISTINCT assigned_supervisor ORDER BY assigned_supervisor ASC SEPARATOR ", ") as assigned_supervisors')
                ->groupBy('date');

            // Apply status filter after grouping (using HAVING clause)
            if ($request->filled('status')) {
                $this->applyStatusFilter($dailyData, $request->status);
            }

            $perPage = $request->get('per_page', 5); // Default to 5, allow 5 or 10
            $perPage = in_array($perPage, [5, 10]) ? $perPage : 5; // Validate input

            $dailyData = $dailyData->orderBy('date', 'desc')
                ->paginate($perPage)
                ->withQueryString();

            // Get available dates and days for filtering (only non-finalized for class scheduling)
        $availableDates = $this->getAvailableDates($request->input('date'), true);
        $availableDays = $this->getAvailableDays();

            return view('schedules.index', compact('dailyData', 'availableDates', 'availableDays'));
        }

    /**
     * Show employee availability with filtering
     */
    private function showEmployeeAvailability(Request $request)
    {
        $query = Tutor::with(['accounts' => function($query) {
            $query->where('account_name', 'GLS');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS');
        });
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tusername', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $request->search . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->search . '%']);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply day filter (optional)
        if ($request->filled('day')) {
            $dayName = $this->normalizeDayName($request->day);
            $dayNameLower = strtolower($dayName);
            
            
            $query->whereHas('accounts', function($q) use ($dayName, $dayNameLower) {
                $q->where('account_name', 'GLS')
                  ->where(function($subQuery) use ($dayName, $dayNameLower) {
                      $subQuery->where('available_times', 'like', '%' . $dayName . '%')
                               ->orWhere('available_times', 'like', '%' . $dayNameLower . '%');
                  });
            });
        }
        
        // Apply time slot filter (optional)
        if ($request->filled('time_slot')) {
            $timeSlot = $request->time_slot;
            // Normalize time format - convert "09:00 - 10:00" to "09:00-10:00"
            $timeSlot = str_replace([' - ', ' '], '-', $timeSlot);
            
            // Parse the requested time range
            if (strpos($timeSlot, '-') !== false) {
                list($requestedStart, $requestedEnd) = explode('-', $timeSlot);
                
                // Convert to minutes for comparison
                $requestedStartMinutes = $this->timeToMinutes($requestedStart);
                $requestedEndMinutes = $this->timeToMinutes($requestedEnd);
                
                // Use a simpler approach: get all tutors and filter in PHP
                $allTutorIds = $query->pluck('tutorID')->toArray();
                
                if (!empty($allTutorIds)) {
                    $filteredTutorIds = [];
                    
                    // Get tutors with their accounts
                $tutorsWithAccounts = Tutor::with(['accounts' => function($query) {
                        $query->where('account_name', 'GLS');
                    }])->whereIn('tutorID', $allTutorIds)->get();
                    
                    foreach ($tutorsWithAccounts as $tutor) {
                        $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
                        $requestedDay = $request->filled('day') ? $this->normalizeDayName($request->day) : null;
                        if ($glsAccount && $this->isTimeRangeAvailable($glsAccount->available_times, $requestedStart, $requestedEnd, $requestedDay)) {
                            $filteredTutorIds[] = $tutor->tutorID;
                        }
                    }
                    
                    // Apply the filtered tutor IDs
                    if (!empty($filteredTutorIds)) {
                        $query->whereIn('tutorID', $filteredTutorIds);
                    } else {
                        // No tutors match the time range, return empty result
                        $query->where('tutorID', '=', 'impossible_id_that_does_not_exist');
                    }
                }
            } else {
                // Fallback to simple string matching for single time values
                $query->whereHas('accounts', function($q) use ($timeSlot) {
                    $q->where('account_name', 'GLS')
                      ->where('available_times', 'like', '%' . $timeSlot . '%');
                });
            }
        }
        
        $perPage = $request->get('per_page', 5); // Default to 5, allow 5 or 10
        $perPage = in_array($perPage, [5, 10]) ? $perPage : 5; // Validate input
        
        $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate($perPage)->withQueryString();
        $availableTimeSlots = $this->getAvailableTimeSlots();
        $availableDays = $this->getAvailableDaysFromTutorAccounts();
        
        return view('schedules.index', compact('tutors', 'availableTimeSlots', 'availableDays'));
    }

    /**
     * Check if a requested time range falls within any of the tutor's available time ranges
     */
    private function isTimeRangeAvailable($availableTimes, $requestedStart, $requestedEnd, $requestedDay = null)
    {
        if (empty($availableTimes)) {
            return false;
        }

        // Convert requested times to minutes for easier comparison
        $requestedStartMinutes = $this->timeToMinutes($requestedStart);
        $requestedEndMinutes = $this->timeToMinutes($requestedEnd);

        // Handle array format of available_times
        if (is_array($availableTimes)) {
            // If available_times is an array, iterate through each day
            foreach ($availableTimes as $day => $times) {
                // If a specific day is requested, only check that day
                if ($requestedDay && strtolower($day) !== strtolower($requestedDay)) {
                    continue;
                }
                
                if (!is_array($times)) {
                    $times = [$times];
                }
                
                foreach ($times as $timeRange) {
                    if (strpos($timeRange, '-') !== false) {
                        list($startTime, $endTime) = explode('-', $timeRange);
                        
                        $startMinutes = $this->timeToMinutes($startTime);
                        $endMinutes = $this->timeToMinutes($endTime);
                        
                        // Check if the requested time range is completely within this available time range
                        // The requested range must be fully contained: requestedStart >= availableStart AND requestedEnd <= availableEnd
                        if ($requestedStartMinutes >= $startMinutes && $requestedEndMinutes <= $endMinutes) {
                            return true;
                        }
                    }
                }
            }
        } else {
            // Handle string format of available_times
            $availableTimes = (string)$availableTimes;
            
            // Parse the available_times string to extract time ranges
            // Format is typically: "mon: 09:00-11:00, tue: 14:00-16:00, wed: 10:00-12:00"
            $entries = explode(',', $availableTimes);
            
            foreach ($entries as $entry) {
                $entry = trim($entry);
                
                // Look for time patterns like "09:00-11:00" or "14:00 - 16:00"
                if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $entry, $matches)) {
                    $startTime = $matches[1];
                    $endTime = $matches[2];
                    
                    $startMinutes = $this->timeToMinutes($startTime);
                    $endMinutes = $this->timeToMinutes($endTime);
                    
                    // Check if the requested time range is completely within this available time range
                    // The requested range must be fully contained: requestedStart >= availableStart AND requestedEnd <= availableEnd
                    if ($requestedStartMinutes >= $startMinutes && $requestedEndMinutes <= $endMinutes) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Convert time string (HH:MM) to minutes since midnight
     */
    private function timeToMinutes($timeString)
    {
        $time = explode(':', trim($timeString));
        $hours = (int) $time[0];
        $minutes = (int) $time[1];
        return $hours * 60 + $minutes;
    }

    /**
     * Show per-day schedule view
     */
    public function showPerDaySchedule($date, $page = 1)
    {
        // Return the view directly instead of redirecting to avoid infinite loop
        return $this->showPerDayScheduleData($date, $page, null);
    }

    /**
     * Show per-day schedule data (private method for data fetching)
     */
    private function showPerDayScheduleData($date, $page = 1, $request = null)
    {
        // Get all classes for the specific date with tutor assignments (paginated)
        $dayClasses = DailyData::where('date', $date)
            ->with(['tutorAssignments' => function($query) {
                $query->where('status', '!=', 'cancelled')->with('tutor');
            }])
                ->orderBy('school')
                ->orderBy('time_jst')
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString();

        // Check if this schedule is finalized
        $isFinalized = $dayClasses->where('schedule_status', 'finalized')->count() > 0;
        $finalizedAt = $isFinalized ? $dayClasses->where('schedule_status', 'finalized')->first()->finalized_at : null;

        // Get grouped information for the header (include all classes for statistics)
        $dayInfo = DailyData::select([
            'date',
            DB::raw('DAYNAME(date) as day'),
            DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
            DB::raw('COUNT(*) as class_count'),
            DB::raw('SUM(number_required) as total_required')
        ])
        ->where('date', $date)
        ->groupBy('date')
        ->first();

        // If no data found, create a fallback with basic date info
        if (!$dayInfo) {
            $dayInfo = (object) [
                'date' => $date,
                'day' => \Carbon\Carbon::parse($date)->format('l'),
                'schools' => 'No classes found',
                'class_count' => 0,
                'total_required' => 0
            ];
        }

        // Get available tutors for this day
        $availableTutors = Tutor::with(['accounts' => function($query) {
            $query->where('account_name', 'GLS');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS');
        })
        ->where('status', 'active')
        ->get();

        // Get available time slots
        $availableTimeSlots = $this->getAvailableTimeSlots();

        // Get available dates and days for filtering (only non-finalized for class scheduling)
        $availableDates = $this->getAvailableDates($request ? $request->input('date') : null, true);
        $availableDays = $this->getAvailableDays();

        return view('schedules.index', compact(
            'dayClasses', 
            'isFinalized', 
            'finalizedAt', 
            'dayInfo', 
            'availableTutors', 
            'availableTimeSlots',
            'availableDates',
            'availableDays'
        ));
    }

    /**
     * Show schedule history (finalized schedules)
     */
    public function showScheduleHistory(Request $request)
    {
        // Redirect to main index with history tab to maintain proper layout
        return redirect()->route('schedules.index', array_merge($request->all(), ['tab' => 'history']));
    }

    /**
     * Show schedule history data (finalized schedules) within main index
     */
    private function showScheduleHistoryData(Request $request)
    {
        $query = DailyData::query();
        
        // Only show finalized schedules
        $query->where('schedule_status', 'finalized');

        // Apply filters (mutually exclusive: date takes precedence over day)
        if ($request->filled('search')) {
            $query->where('school', 'like', '%' . $request->search . '%');
        }

        $dateFilter = $request->input('date');
        $dayFilter = $request->input('day');

        if ($dateFilter && trim($dateFilter) !== '') {
            $query->where(function($q) use ($dateFilter) {
                $q->whereDate('date', $dateFilter)
                  ->orWhere('date', $dateFilter);
            });
        } elseif ($dayFilter && trim($dayFilter) !== '') {
            $day = $this->validateDayName($dayFilter);
            if ($day) {
                $dayOfWeek = $this->getDayOfWeek($day);
                if ($dayOfWeek) {
                    // Match by numeric day-of-week or by day name for broader compatibility
                    $query->where(function($q) use ($dayOfWeek, $day) {
                        $q->whereRaw('DAYOFWEEK(date) = ?', [$dayOfWeek])
                          ->orWhereRaw('UPPER(DAYNAME(date)) = ?', [strtoupper($day)]);
                    });
                }
            }
        }

        $scheduleHistory = $query->selectRaw('date, DAYNAME(date) as day, 
            GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
            COUNT(*) as class_count,
            SUM(number_required) as total_required,
            (SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned,
            MAX(finalized_at) as finalized_at')
            ->groupBy('date')
            ->orderBy('date', 'desc');

        $perPage = $request->get('per_page', 5); // Default to 5, allow 5 or 10
        $perPage = in_array($perPage, [5, 10]) ? $perPage : 5; // Validate input

        $scheduleHistory = $scheduleHistory->paginate($perPage)->withQueryString();

        // Get available dates and days for filtering (only finalized schedules)
        $availableDates = DailyData::where('schedule_status', 'finalized')
            ->select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        // Show all weekdays for filtering (not based on actual data)
        $availableDays = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);

        return view('schedules.index', compact('scheduleHistory', 'availableDates', 'availableDays'));
    }

    /**
     * Search schedules
     */
    public function searchSchedules(Request $request)
    {
        try {
            
            $query = DailyData::query();
            
            // Only show non-finalized schedules
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'finalized')
                  ->orWhereNull('schedule_status');
            });
            
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('school', 'like', '%' . $searchTerm . '%')
                      ->orWhere('class', 'like', '%' . $searchTerm . '%')
                      ->orWhere('assigned_supervisor', 'like', '%' . $searchTerm . '%');
                });
            }
            
            // Prioritize date filter over day filter - only apply one at a time
            $dateFilter = $request->input('date');
            $dayFilter = $request->input('day');
            
            if ($dateFilter && trim($dateFilter) !== '') {
                // Try both whereDate and direct comparison
                $query->where(function($q) use ($dateFilter) {
                    $q->whereDate('date', $dateFilter)
                      ->orWhere('date', $dateFilter);
                });
            } elseif ($dayFilter && trim($dayFilter) !== '') {
                $day = $this->validateDayName($dayFilter);
                if ($day) {
                    // Use DAYOFWEEK instead of DAYNAME for more reliable filtering
                    $dayOfWeek = $this->getDayOfWeek($day);
                    if ($dayOfWeek) {
                        $query->whereRaw('DAYOFWEEK(date) = ?', [$dayOfWeek]);
                    }
                }
            }
            
            $schedules = $query->selectRaw('date, DAYNAME(date) as day, 
                GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools,
                COUNT(*) as class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 0 ELSE 1 END) as active_class_count,
                SUM(CASE WHEN class_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_class_count,
                SUM(number_required) as total_required,
                (SELECT COUNT(*) FROM tutor_assignments ta WHERE ta.daily_data_id IN (SELECT dd2.id FROM daily_data dd2 WHERE dd2.date = daily_data.date) AND (ta.is_backup = 0 OR ta.is_backup IS NULL)) as total_assigned,
                GROUP_CONCAT(DISTINCT assigned_supervisor ORDER BY assigned_supervisor ASC SEPARATOR ", ") as assigned_supervisors')
                ->groupBy('date');

            // Apply status filter after grouping (using HAVING clause)
            if ($request->filled('status')) {
                $this->applyStatusFilter($schedules, $request->status);
            }

            $perPage = $request->get('per_page', 5); // Default to 5, allow 5 or 10
            $perPage = in_array($perPage, [5, 10]) ? $perPage : 5; // Validate input

            $schedules = $schedules->orderBy('date', 'desc')
                ->paginate($perPage)
                ->withQueryString();
            
            // Check if this is an AJAX request
            if ($request->ajax()) {
                // Return JSON response for AJAX requests
                $html = view('schedules.partials.class-schedule-table', ['schedules' => $schedules])->render();
                $pagination = view('schedules.partials.compact-class-pagination', ['dailyData' => $schedules])->render();
            
            return response()->json([
                'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'total' => $schedules->total()
            ]);
            }

            // Get available dates and days for filtering (only non-finalized for class scheduling)
            $availableDates = $this->getAvailableDates($request->input('date'), true);
            $availableDays = $this->getAvailableDays();
            
            // Rename $schedules to $dailyData to match view expectations
            $dailyData = $schedules;
            
            return view('schedules.index', compact('dailyData', 'availableDates', 'availableDays'));
            
        } catch (\Exception $e) {
            Log::error('Error searching schedules: ' . $e->getMessage());
            return redirect()->route('schedules.index', ['tab' => 'class'])
                ->with('error', 'An error occurred while searching schedules.');
        }
    }

    /**
     * Save schedule with specified status (tentative or final)
     */
    public function saveSchedule(SaveScheduleRequest $request)
    {
        try {
            $date = $request->input('date');
            $status = $request->input('status');

            $supervisor = Supervisor::first();
            $performedBy = $supervisor ? $supervisor->supID : null;

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
            
            // Log schedule save activity
            $currentUser = $this->getCurrentAuthenticatedUser();
            \App\Models\AuditLog::logEvent(
                $status === 'final' ? 'schedule_finalized' : 'schedule_saved_tentative',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                $status === 'final' ? 'Schedule Finalized' : 'Schedule Saved as Tentative',
                "Schedule for {$date} saved as " . ($status === 'final' ? 'finalized' : 'tentative') . " by {$currentUser['name']}. {$updatedCount} classes updated.",
                ['date' => $date, 'status' => $scheduleStatus, 'updated_count' => $updatedCount],
                $status === 'final' ? 'high' : 'medium',
                true
            );
            
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
     * Cancel a class (mark as cancelled and remove tutor assignments)
     */
    public function cancelClass(Request $request, $classId)
    {
        try {
            // Validate the request
            $request->validate([
                'cancellation_reason' => 'required|string|max:1000'
            ], [
                'cancellation_reason.required' => 'Cancellation reason is required.',
                'cancellation_reason.string' => 'Cancellation reason must be text.',
                'cancellation_reason.max' => 'Cancellation reason cannot exceed 1000 characters.'
            ]);

            $class = DailyData::findOrFail($classId);
            
            // Check ownership
            $currentSupervisorId = $this->getCurrentSupervisorId();
            
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
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason
            ]);

            // Log class cancellation to audit system
            $currentUser = $this->getCurrentAuthenticatedUser();
            AuditLog::logEvent(
                'class_cancellation',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Class Cancelled',
                "Class {$class->class} at {$class->school} on {$class->date} cancelled by {$currentUser['name']}. Reason: {$request->cancellation_reason}",
                ['class_id' => $classId, 'school' => $class->school, 'date' => $class->date, 'reason' => $request->cancellation_reason],
                'high',
                true
            );

            // Create history record for the cancellation
            $class->createHistoryRecord(
                'class_cancelled',
                $currentSupervisorId,
                $request->cancellation_reason,
                ['class_status' => 'active'],
                ['class_status' => 'cancelled', 'cancellation_reason' => $request->cancellation_reason]
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Class cancelled successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
     * Toggle tutor status (active/inactive)
     */
    public function toggleTutorStatus(Request $request, Tutor $tutor)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            $newStatus = $request->status;
            $oldStatus = $tutor->status;
            $tutor->update(['status' => $newStatus]);

            $tutorName = $tutor->full_name ?? 'Tutor';
            $actionText = $newStatus === 'active' ? 'activated and is now available for class assignments' : 'deactivated and will no longer receive class assignments';
            
            // Get current user info
            $currentUser = $this->getCurrentAuthenticatedUser();
            
            // Log the activity with appropriate severity
            \App\Models\AuditLog::logEvent(
                $newStatus === 'active' ? 'tutor_activated' : 'tutor_deactivated', // eventType
                $currentUser['type'], // userType
                $currentUser['id'], // userId
                $currentUser['email'], // userEmail
                $currentUser['name'], // userName
                $newStatus === 'active' ? 'Tutor Activated' : 'Tutor Deactivated', // action
                "Tutor {$tutorName} ({$tutor->tutorID}) status changed from {$oldStatus} to {$newStatus}", // description
                ['tutor_id' => $tutor->tutorID, 'old_status' => $oldStatus, 'new_status' => $newStatus], // metadata
                'medium', // severity - Status changes are important operational events
                true // isImportant
            );
            
            return response()->json([
                'success' => true,
                'message' => "{$tutorName} has been {$actionText}",
                'new_status' => $newStatus
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided. Please select either "active" or "inactive".',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error toggling tutor status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to update tutor status. Please try again or contact support if the issue persists.'
            ], 500);
        }
    }

    /**
     * Get available tutors for assignment
     */
    public function getAvailableTutors(Request $request)
    {
        try {
            $date = $request->input('date');
            $day = $request->input('day');
            $timeSlot = $request->input('time_slot');
            $classId = $request->input('class_id');

            // Debug logging
            Log::info('getAvailableTutors called with:', [
                'date' => $date,
                'day' => $day,
                'time_slot' => $timeSlot,
                'class_id' => $classId
            ]);

            // First, let's check how many tutors we have without any filtering
            $totalTutors = Tutor::where('status', 'active')->count();
            $glsTutors = Tutor::whereHas('accounts', function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            })->where('status', 'active')->count();
            
            Log::info('Tutor counts:', [
                'total_active_tutors' => $totalTutors,
                'gls_active_tutors' => $glsTutors
            ]);

            $query = Tutor::with(['accounts' => function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            }])
                           ->whereHas('accounts', function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            })
            ->where('status', 'active');

            // Filter by day if provided
            if ($day) {
                $dayName = $this->normalizeDayName($day);
                Log::info('Filtering by day:', ['original_day' => $day, 'normalized_day' => $dayName]);
                
                // Let's check how many tutors have this day in their available_times
                $dayTutors = Tutor::whereHas('accounts', function($q) use ($dayName) {
                    $q->where('account_name', 'GLS')->where('status', 'active')
                      ->where('available_times', 'like', '%' . $dayName . '%');
                })->where('status', 'active')->count();
                
                Log::info('Tutors available on day:', ['day' => $dayName, 'count' => $dayTutors]);
                
                $query->whereHas('accounts', function($q) use ($dayName) {
                    $q->where('account_name', 'GLS')->where('status', 'active')
                      ->where('available_times', 'like', '%' . $dayName . '%');
                });
            }

            // Filter by time slot if provided
            if ($timeSlot) {
                $timeSlot = str_replace(' - ', '-', $timeSlot);
                Log::info('Filtering by time slot:', ['original_time_slot' => $request->input('time_slot'), 'processed_time_slot' => $timeSlot]);
                
                // Use proper time range validation instead of simple string matching
                $this->filterByTimeSlot($query, $timeSlot, $day);
            }

            // If class_id is provided, exclude only MAIN tutors already assigned to this class
            // But allow backup tutors to be available for promotion to main
            if ($classId) {
                // First, let's see all assignments for this class
                $allAssignments = \App\Models\TutorAssignment::where('daily_data_id', $classId)
                    ->with('tutor')
                    ->get(['tutor_id', 'is_backup']);
                
                Log::info('All assignments for class:', [
                    'class_id' => $classId,
                    'assignments' => $allAssignments->map(function($a) {
                        return [
                            'tutor_id' => $a->tutor_id,
                            'tutor_name' => $a->tutor ? $a->tutor->full_name : 'Unknown',
                            'is_backup' => $a->is_backup
                        ];
                    })
                ]);
                
                $assignedMainTutorIds = \App\Models\TutorAssignment::where('daily_data_id', $classId)
                    ->where('is_backup', false) // Only exclude main tutors, not backup tutors
                    ->pluck('tutor_id')
                    ->toArray();
                
                if (!empty($assignedMainTutorIds)) {
                    $query->whereNotIn('tutorID', $assignedMainTutorIds);
                }
                
                Log::info('Excluded main tutors for class:', [
                    'class_id' => $classId,
                    'excluded_main_tutor_ids' => $assignedMainTutorIds
                ]);
            }

            $tutors = $query->get()->map(function($tutor) {
                return [
                    'id' => $tutor->tutorID,
                    'username' => $tutor->tusername,
                        'full_name' => $tutor->full_name,
                    'name' => $tutor->full_name, // Keep for backward compatibility
                    'email' => $tutor->email,
                    'phone' => $tutor->phone_number,
                    'availability' => $tutor->formatted_available_time
                ];
            });

            Log::info('getAvailableTutors result:', [
                'total_tutors_found' => $tutors->count(),
                'tutor_usernames' => $tutors->pluck('username')->toArray()
            ]);

            // If no tutors found with strict time filtering, return empty result
            // This is correct behavior - we should not show tutors who cannot cover the entire class
            if ($tutors->count() === 0 && $timeSlot) {
                Log::info('No tutors found with strict time slot filter - returning empty result');
                
                return response()->json([
                    'success' => true,
                    'tutors' => [],
                    'note' => 'No tutors available for the specified time slot'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'tutors' => $tutors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting available tutors: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search tutors (AJAX endpoint for employee availability)
     */
    public function searchTutors(Request $request)
    {
        try {
            $query = Tutor::with(['accounts' => function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            }])
            ->whereHas('accounts', function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            });
            
            // Apply filters
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('tusername', 'like', '%' . $searchTerm . '%')
                      ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone_number', 'like', '%' . $searchTerm . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchTerm . '%']);
                });
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('time_slot')) {
                $timeSlot = str_replace(' - ', '-', $request->time_slot);
                $query->whereHas('accounts', function($q) use ($timeSlot) {
                    $q->where('account_name', 'GLS')->where('status', 'active')
                      ->where('available_times', 'like', '%' . $timeSlot . '%');
                });
            }
            
            if ($request->filled('day')) {
                $dayName = $this->normalizeDayName($request->day);
                $query->whereHas('accounts', function($q) use ($dayName) {
                    $q->where('account_name', 'GLS')->where('status', 'active')
                      ->where('available_times', 'like', '%' . $dayName . '%');
                });
            }
            
            $perPage = $request->get('per_page', 5); // Default to 5, allow 5 or 10
            $perPage = in_array($perPage, [5, 10]) ? $perPage : 5; // Validate input
            
            $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate($perPage)->withQueryString();
            
            // Render the tutor rows HTML
            $html = view('schedules.tabs.partials.tutor-table-rows', compact('tutors'))->render();
            
            // Render the pagination HTML  
            $pagination = view('schedules.tabs.partials.tutor-pagination', compact('tutors'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'total' => $tutors->total(),
                'current_page' => $tutors->currentPage(),
                'last_page' => $tutors->lastPage()
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching tutors: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods

    /**
     * Normalize day name (convert abbreviated to full)
     */
    private function normalizeDayName($day)
    {
        $day = strtolower(trim($day));
        
        // Handle abbreviated day names
        if (strlen($day) <= 4) {
            $dayMap = [
                'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                'thur' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'
            ];
            return $dayMap[$day] ?? ucfirst($day);
        }
        
        // Handle full day names (lowercase)
        $fullDayMap = [
            'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
            'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'
        ];
        
        return $fullDayMap[$day] ?? ucfirst($day);
    }

    /**
     * Validate and sanitize day name to prevent SQL injection
     */
    private function validateDayName($day)
    {
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $normalizedDay = $this->normalizeDayName($day);
        
        // Only return if it's a valid day name
        if (in_array($normalizedDay, $validDays)) {
            return $normalizedDay;
        }
        
        // Return null for invalid day names
        return null;
    }

    /**
     * Filter tutors by time slot using proper time range validation
     */
    private function filterByTimeSlot($query, $timeSlot, $day = null)
    {
        // Parse the time slot (e.g., "7:30-8:30")
        if (strpos($timeSlot, '-') === false) {
            Log::warning('Invalid time slot format:', ['time_slot' => $timeSlot]);
            return;
        }
        
        [$classStart, $classEnd] = explode('-', $timeSlot);
        $classStart = trim($classStart);
        $classEnd = trim($classEnd);
        
        // Convert to minutes for comparison
        $classStartMinutes = $this->timeToMinutes($classStart);
        $classEndMinutes = $this->timeToMinutes($classEnd);
        
        Log::info('Class time range:', [
            'start' => $classStart,
            'end' => $classEnd,
            'start_minutes' => $classStartMinutes,
            'end_minutes' => $classEndMinutes
        ]);
        
        // Get all tutors with GLS accounts and filter by time availability
        $allTutorIds = $query->pluck('tutorID')->toArray();
        
        if (!empty($allTutorIds)) {
            $filteredTutorIds = [];
            
            // Get tutors with their accounts
            $tutorsWithAccounts = Tutor::with(['accounts' => function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            }])->whereIn('tutorID', $allTutorIds)->get();
            
            foreach ($tutorsWithAccounts as $tutor) {
                $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
                if ($glsAccount && $this->isTutorAvailableForTimeSlot($glsAccount->available_times, $classStartMinutes, $classEndMinutes, $day)) {
                    $filteredTutorIds[] = $tutor->tutorID;
                }
            }
            
            // Apply the filtered tutor IDs
            if (!empty($filteredTutorIds)) {
                $query->whereIn('tutorID', $filteredTutorIds);
            } else {
                // No tutors match the time range, return empty result
                $query->where('tutorID', '=', 'impossible_id_that_does_not_exist');
            }
        }
    }
    
    /**
     * Check if tutor is available for the entire class duration
     */
    private function isTutorAvailableForTimeSlot($availableTimes, $classStartMinutes, $classEndMinutes, $day = null)
    {
        if (!$availableTimes) {
            return false;
        }
        
        $times = is_string($availableTimes) ? json_decode($availableTimes, true) : $availableTimes;
        
        if (!$times || !is_array($times)) {
            return false;
        }
        
        // If day is specified, only check that day
        if ($day) {
            $dayName = $this->normalizeDayName($day);
            $dayTimes = $times[$dayName] ?? [];
            
            foreach ($dayTimes as $timeRange) {
                if ($this->isTimeRangeFullyCovers($timeRange, $classStartMinutes, $classEndMinutes)) {
                    return true;
                }
            }
            return false;
        }
        
        // Check all days
        foreach ($times as $dayTimes) {
            if (is_array($dayTimes)) {
                foreach ($dayTimes as $timeRange) {
                    if ($this->isTimeRangeFullyCovers($timeRange, $classStartMinutes, $classEndMinutes)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a time range can cover the class duration
     */
    private function isTimeRangeFullyCovers($timeRange, $classStartMinutes, $classEndMinutes)
    {
        // Parse the time range (e.g., "08:00    -    09:30")
        $timeRange = preg_replace('/\s+/', ' ', trim($timeRange));
        
        if (strpos($timeRange, '-') === false) {
            return false;
        }
        
        [$tutorStart, $tutorEnd] = explode('-', $timeRange);
        $tutorStart = trim($tutorStart);
        $tutorEnd = trim($tutorEnd);
        
        $tutorStartMinutes = $this->timeToMinutes($tutorStart);
        $tutorEndMinutes = $this->timeToMinutes($tutorEnd);
        
        // Calculate class duration
        $classDuration = $classEndMinutes - $classStartMinutes;
        $tutorDuration = $tutorEndMinutes - $tutorStartMinutes;
        
        // Check if tutor can cover the class start time
        if ($classStartMinutes < $tutorStartMinutes) {
            return false; // Class starts before tutor is available
        }
        
        // Check if tutor can cover at least 50% of the class duration
        $availableDuration = $tutorEndMinutes - $classStartMinutes;
        $coveragePercentage = $availableDuration / $classDuration;
        
        // Allow tutors who can cover at least 50% of the class duration
        // This provides more flexibility for real-world scheduling scenarios
        return $coveragePercentage >= 0.5;
    }
    
    /**
     * Filter tutors by time slot using relaxed time matching (any overlap)
     */
    private function filterByTimeSlotRelaxed($query, $timeSlot, $day = null)
    {
        // Parse the time slot (e.g., "7:30-8:30")
        if (strpos($timeSlot, '-') === false) {
            Log::warning('Invalid time slot format:', ['time_slot' => $timeSlot]);
            return;
        }
        
        [$classStart, $classEnd] = explode('-', $timeSlot);
        $classStart = trim($classStart);
        $classEnd = trim($classEnd);
        
        // Convert to minutes for comparison
        $classStartMinutes = $this->timeToMinutes($classStart);
        $classEndMinutes = $this->timeToMinutes($classEnd);
        
        Log::info('Relaxed filtering - Class time range:', [
            'start' => $classStart,
            'end' => $classEnd,
            'start_minutes' => $classStartMinutes,
            'end_minutes' => $classEndMinutes
        ]);
        
        // Get all tutors with GLS accounts and filter by time availability
        $allTutorIds = $query->pluck('tutorID')->toArray();
        
        if (!empty($allTutorIds)) {
            $filteredTutorIds = [];
            
            // Get tutors with their accounts
            $tutorsWithAccounts = Tutor::with(['accounts' => function($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            }])->whereIn('tutorID', $allTutorIds)->get();
            
            foreach ($tutorsWithAccounts as $tutor) {
                $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
                if ($glsAccount && $this->isTutorAvailableForTimeSlotRelaxed($glsAccount->available_times, $classStartMinutes, $classEndMinutes, $day)) {
                    $filteredTutorIds[] = $tutor->tutorID;
                }
            }
            
            // Apply the filtered tutor IDs
            if (!empty($filteredTutorIds)) {
                $query->whereIn('tutorID', $filteredTutorIds);
            } else {
                // No tutors match the time range, return empty result
                $query->where('tutorID', '=', 'impossible_id_that_does_not_exist');
            }
        }
    }
    
    /**
     * Check if tutor has any time overlap with the class (relaxed matching)
     */
    private function isTutorAvailableForTimeSlotRelaxed($availableTimes, $classStartMinutes, $classEndMinutes, $day = null)
    {
        if (!$availableTimes) {
            return false;
        }
        
        $times = is_string($availableTimes) ? json_decode($availableTimes, true) : $availableTimes;
        
        if (!$times || !is_array($times)) {
            return false;
        }
        
        // If day is specified, only check that day
        if ($day) {
            $dayName = $this->normalizeDayName($day);
            $dayTimes = $times[$dayName] ?? [];
            
            foreach ($dayTimes as $timeRange) {
                if ($this->isTimeRangeOverlaps($timeRange, $classStartMinutes, $classEndMinutes)) {
                    return true;
                }
            }
            return false;
        }
        
        // Check all days
        foreach ($times as $dayTimes) {
            if (is_array($dayTimes)) {
                foreach ($dayTimes as $timeRange) {
                    if ($this->isTimeRangeOverlaps($timeRange, $classStartMinutes, $classEndMinutes)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a time range has any overlap with the class duration
     */
    private function isTimeRangeOverlaps($timeRange, $classStartMinutes, $classEndMinutes)
    {
        // Parse the time range (e.g., "08:00    -    09:30")
        $timeRange = preg_replace('/\s+/', ' ', trim($timeRange));
        
        if (strpos($timeRange, '-') === false) {
            return false;
        }
        
        [$tutorStart, $tutorEnd] = explode('-', $timeRange);
        $tutorStart = trim($tutorStart);
        $tutorEnd = trim($tutorEnd);
        
        $tutorStartMinutes = $this->timeToMinutes($tutorStart);
        $tutorEndMinutes = $this->timeToMinutes($tutorEnd);
        
        // Check for any overlap: (classStart < tutorEnd) AND (classEnd > tutorStart)
        return ($classStartMinutes < $tutorEndMinutes) && ($classEndMinutes > $tutorStartMinutes);
    }
    

    /**
     * Get day of week number for MySQL DAYOFWEEK function
     * DAYOFWEEK returns 1=Sunday, 2=Monday, 3=Tuesday, 4=Wednesday, 5=Thursday, 6=Friday, 7=Saturday
     */
    private function getDayOfWeek($day)
    {
        $dayMap = [
            'Sunday' => 1,
            'Monday' => 2,
            'Tuesday' => 3,
            'Wednesday' => 4,
            'Thursday' => 5,
            'Friday' => 6,
            'Saturday' => 7
        ];
        
        return $dayMap[$day] ?? null;
    }

    /**
     * Apply status filter to query
     */
    private function applyStatusFilter($query, $status)
    {
        switch ($status) {
            case 'fully_assigned':
                $query->havingRaw('total_assigned >= total_required');
                    break;
            case 'partially_assigned':
                $query->havingRaw('total_assigned > 0 AND total_assigned < total_required');
                break;
            case 'not_assigned':
                $query->havingRaw('total_assigned = 0');
                break;
            case 'assigned':
                $query->havingRaw('total_assigned > 0');
                break;
            case 'unassigned':
                $query->havingRaw('total_assigned = 0');
                break;
            case 'cancelled':
                $query->havingRaw('cancelled_class_count > 0');
                break;
        }
    }

    /**
     * Get available dates for filtering
     */
    private function getAvailableDates($includeSelectedDate = null, $nonFinalizedOnly = false)
    {
        $query = DailyData::select('date')->distinct();
        
        // For class scheduling tab, only show dates with non-finalized schedules
        if ($nonFinalizedOnly) {
            // Debug: Let's see what status values exist
            $allStatuses = DailyData::select('schedule_status')->distinct()->pluck('schedule_status');
            Log::info('Available schedule statuses in database:', ['statuses' => $allStatuses->toArray()]);
            
            $query->where(function($q) {
                $q->where('schedule_status', '!=', 'finalized')
                  ->orWhereNull('schedule_status');
            });
        }
        
        $dates = $query->orderBy('date', 'desc')->pluck('date')->map(function($date) {
            return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
        });
        
        Log::info('getAvailableDates result:', [
            'nonFinalizedOnly' => $nonFinalizedOnly,
            'total_dates_found' => $dates->count(),
            'dates' => $dates->toArray(),
            'first_date_type' => $dates->first() ? gettype($dates->first()) : 'null',
            'first_date_value' => $dates->first()
        ]);
        
        // If a specific date is selected, make sure it's included in the dropdown
        if ($includeSelectedDate && !$dates->contains($includeSelectedDate)) {
            $dates = $dates->prepend($includeSelectedDate)->unique()->sort()->reverse();
        }
        
        return $dates;
    }

    /**
     * Get available days for filtering
     */
    private function getAvailableDays()
    {
        $dates = DailyData::where(function($q) {
            $q->where('schedule_status', '!=', 'finalized')
              ->orWhereNull('schedule_status');
        })
        ->select('date')
        ->distinct()
        ->pluck('date');
        
        // Convert dates to day names using Carbon
        $days = $dates->map(function($date) {
            try {
                return \Carbon\Carbon::parse($date)->format('l'); // 'l' gives full day name
            } catch (\Exception $e) {
                return null;
            }
        })->filter()->unique();
        
        // Filter out weekends and sort days in chronological order (weekdays only)
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $filteredDays = $days->filter(function($day) use ($weekdays) {
            return in_array($day, $weekdays);
        });
        
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        return $filteredDays->sortBy(function($day) use ($dayOrder) {
            return array_search($day, $dayOrder);
        })->values();
    }

    /**
     * Get available days from tutor accounts for employee availability filtering
     */
    private function getAvailableDaysFromTutorAccounts()
    {
        $tutorAccounts = \App\Models\TutorAccount::where('status', 'active')
            ->whereNotNull('available_days')
            ->get();
        
        $allDays = collect();
        
        foreach ($tutorAccounts as $account) {
            $availableDays = $account->available_days;
            
            // Handle case where available_days might be a string instead of array
            if (is_string($availableDays)) {
                $availableDays = json_decode($availableDays, true) ?? [];
            }
            
            if (is_array($availableDays)) {
                $allDays = $allDays->merge($availableDays);
            }
        }
        
        // Get unique days and filter out empty values and weekends
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $uniqueDays = $allDays->filter()->unique()->filter(function($day) use ($weekdays) {
            return in_array($day, $weekdays);
        })->values();
        
        // Sort days in chronological order (weekdays only)
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        return $uniqueDays->sortBy(function($day) use ($dayOrder) {
            return array_search($day, $dayOrder);
        })->values();
    }

    /**
     * Get available time slots
     */
    private function getAvailableTimeSlots()
    {
        return collect([
            '07:00 - 08:00',
            '08:00 - 09:00',
            '09:00 - 10:00',
            '10:00 - 11:00',
            '11:00 - 12:00',
            '12:00 - 13:00',
            '13:00 - 14:00',
            '14:00 - 15:00',
            '15:00 - 16:00',
            '16:00 - 17:00',
            '17:00 - 18:00',
            '18:00 - 19:00',
            '19:00 - 20:00',
            '20:00 - 21:00'
        ]);
    }

    /**
     * Get current supervisor ID
     */
    private function getCurrentSupervisorId()
    {
            if (Auth::guard('supervisor')->check()) {
            return Auth::guard('supervisor')->user()->supID;
            } elseif (session('supervisor_id')) {
            return session('supervisor_id');
        }
        return null;
    }

    /**
     * Check if a tutor has time conflicts for a specific date and time
     */
    public function checkTutorTimeConflict(Request $request)
    {
        try {
            $tutorUsername = $request->input('tutor_username');
            $date = $request->input('date');
            $timeSlot = $request->input('time_slot');
            $excludeClassId = $request->input('exclude_class_id'); // To exclude current class when editing

            if (!$tutorUsername || !$date || !$timeSlot) {
                return response()->json([
                    'has_conflict' => false,
                    'message' => 'Missing required parameters'
                ], 400);
            }

            // Find the tutor
            $tutor = Tutor::where('tusername', $tutorUsername)->first();
            if (!$tutor) {
                return response()->json([
                    'has_conflict' => false,
                    'message' => 'Tutor not found'
                ], 404);
            }

            // Parse the time slot to get start and end times
            // Expected format: "14:00 - 15:00" or "2:00 PM - 3:00 PM"
            $timeSlotParts = explode(' - ', $timeSlot);
            if (count($timeSlotParts) !== 2) {
                return response()->json([
                    'has_conflict' => false,
                    'message' => 'Invalid time slot format'
                ], 400);
            }

            $startTime = trim($timeSlotParts[0]);
            $endTime = trim($timeSlotParts[1]);

            // Convert to 24-hour format if needed
            $startTime24 = $this->convertTo24Hour($startTime);
            $endTime24 = $this->convertTo24Hour($endTime);

            // Check for conflicts in the database
            $conflictQuery = DB::table('daily_data as dd')
                ->join('tutor_assignments as ta', 'dd.id', '=', 'ta.class_id')
                ->where('ta.tutor_id', $tutor->tutorID)
                ->where('dd.date', $date)
                ->where('dd.class_status', '!=', 'cancelled') // Exclude cancelled classes
                ->whereRaw("TIME(dd.time_jst) BETWEEN ? AND ?", [$startTime24, $endTime24]);

            // Exclude current class if editing
            if ($excludeClassId) {
                $conflictQuery->where('dd.id', '!=', $excludeClassId);
            }

            $conflicts = $conflictQuery->get();

            $hasConflict = $conflicts->count() > 0;
            $conflictDetails = [];

            if ($hasConflict) {
                foreach ($conflicts as $conflict) {
                    $conflictDetails[] = [
                        'class' => $conflict->class ?? 'Unknown',
                        'school' => $conflict->school ?? 'Unknown',
                        'time' => $conflict->time_jst ? \Carbon\Carbon::parse($conflict->time_jst)->format('g:i A') : 'Unknown'
                    ];
                }
            }

            return response()->json([
                'has_conflict' => $hasConflict,
                'conflicts' => $conflictDetails,
                'message' => $hasConflict 
                    ? 'Tutor has ' . count($conflictDetails) . ' time conflict(s) on this date'
                    : 'No time conflicts found'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking tutor time conflict: ' . $e->getMessage());
            return response()->json([
                'has_conflict' => false,
                'message' => 'Error checking conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert time to 24-hour format
     */
    private function convertTo24Hour($time)
    {
        try {
            // If already in 24-hour format (contains colon but no AM/PM)
            if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                return $time;
            }
            
            // If in 12-hour format with AM/PM
            if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time, $matches)) {
                $hour = (int)$matches[1];
                $minute = $matches[2];
                $ampm = strtoupper($matches[3]);
                
                if ($ampm === 'PM' && $hour !== 12) {
                    $hour += 12;
                } elseif ($ampm === 'AM' && $hour === 12) {
                    $hour = 0;
                }
                
                return sprintf('%02d:%s', $hour, $minute);
            }
            
            // If just a number, assume it's hour in 24-hour format
            if (is_numeric($time)) {
                return sprintf('%02d:00', $time);
            }
            
            return $time; // Return as-is if can't parse
        } catch (\Exception $e) {
            return $time; // Return as-is if error
        }
    }

    /**
     * Get current authenticated user information across different guards
     */
    private function getCurrentAuthenticatedUser()
    {
        // Check supervisor guard first
        if (Auth::guard('supervisor')->check()) {
            $user = Auth::guard('supervisor')->user();
            return [
                'type' => 'supervisor',
                'id' => $user->supID,
                'email' => $user->semail,
                'name' => $user->full_name ?? ($user->sfname . ' ' . $user->slname)
            ];
        }
        
        // Check web guard (admin users)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return [
                'type' => 'admin',
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ];
        }
        
        // Check tutor guard
        if (Auth::guard('tutor')->check()) {
            $user = Auth::guard('tutor')->user();
            return [
                'type' => 'tutor',
                'id' => $user->tutorID,
                'email' => $user->email,
                'name' => $user->full_name ?? ($user->tfname . ' ' . $user->tlname)
            ];
        }
        
        // Fallback to system if no authenticated user found
        return [
            'type' => 'system',
            'id' => 'system',
            'email' => 'system@ogsconnect.com',
            'name' => 'System Admin'
        ];
    }
}
