<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\ScheduleDailyData;
use App\Models\AssignedDailyData;
use App\Models\DailyData; // Keep for backward compatibility during transition
use App\Models\ScheduleHistory;
use App\Models\Supervisor;
use App\Models\SupervisorWatch;
use App\Models\Notification;
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

            // Employee availability tab - DEFAULT TAB (no redirect loop)
            // Always show employee availability view, never redirect
            return $this->showEmployeeAvailability($request);
            
        } catch (\Exception $e) {
            Log::error('Error in ScheduleController@index: ' . $e->getMessage());
            // Show error on employee availability tab instead of redirecting
            return $this->showEmployeeAvailability($request)
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Show class schedule list with filtering
     */
    private function showClassScheduleList(Request $request)
    {
        $query = ScheduleDailyData::query()
            ->leftJoin('assigned_daily_data as ad', function($join) {
                $join->on('ad.schedule_daily_data_id', '=', 'schedules_daily_data.id')
                     ->whereRaw('ad.id = (
                        SELECT MAX(ad2.id)
                        FROM assigned_daily_data ad2
                        WHERE ad2.schedule_daily_data_id = schedules_daily_data.id
                     )');
            });

        // Apply search filter (school name)
        if ($request->filled('search')) {
            $query->where('school', 'like', '%' . $request->search . '%');
        }

        // Date filter
        if ($request->filled('date') && trim($request->input('date')) !== '') {
            $query->whereDate('date', $request->input('date'));
        }
        // Day filter (only if date not set)
        elseif ($request->filled('day') && trim($request->input('day')) !== '') {
            $day = $this->validateDayName($request->input('day'));
            if ($day) {
                $dayOfWeek = $this->getDayOfWeek($day);
                if ($dayOfWeek) {
                    $query->whereRaw('DAYOFWEEK(date) = ?', [$dayOfWeek]);
                }
            }
        }
        
        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'not_assigned') {
                // Show schedules with no assignment or with not_assigned status
                $query->where(function($q) {
                    $q->whereDoesntHave('assignedData')
                      ->orWhereHas('assignedData', function($sub) {
                          $sub->where('class_status', 'not_assigned');
                      });
                });
            } else {
                // Show schedules with specific status
                $query->whereHas('assignedData', function($sub) use ($status) {
                    $sub->where('class_status', $status);
                });
            }
        } else {
            // If no status filter, show all schedules but exclude fully_assigned with past dates
            // Fully_assigned schedules with past dates go to schedule history
            $query->where(function($q) {
                $q->whereDoesntHave('assignedData')
                  ->orWhereHas('assignedData', function($sub) {
                      $sub->where('class_status', '!=', 'fully_assigned');
                  })
                  ->orWhere(function($fullyAssigned) {
                      $fullyAssigned->whereHas('assignedData', function($sub) {
                          $sub->where('class_status', 'fully_assigned');
                      })->where('date', '>=', now()->toDateString());
                  });
            });
        }

        // Apply sorting
        $sortField = $request->input('sort');
        $sortDirection = $request->input('direction', 'asc');
        
        // Validate sort field and direction
        $allowedSorts = ['date', 'day', 'time', 'school', 'status'];
        
        // Only apply custom sorting if sort field is provided and valid
        if ($sortField && in_array($sortField, $allowedSorts)) {
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
            
            // Map status sort to the actual column
            if ($sortField === 'status') {
                $query->orderBy(DB::raw('ad.class_status'), $sortDirection);
            } else {
                $query->orderBy('schedules_daily_data.' . $sortField, $sortDirection);
            }
            
            // Add secondary sort by time if not sorting by time
            if ($sortField !== 'time') {
                $query->orderBy('schedules_daily_data.time', 'asc');
            }
        } else {
            // Default sorting when no sort parameter or after reset
            $query->orderBy('schedules_daily_data.date', 'asc');
            $query->orderBy('schedules_daily_data.time', 'asc');
        }
        
        // Get individual schedule entries (no grouping - show each schedule as separate row)
        $dailyData = $query->select(
                'schedules_daily_data.id',
                'schedules_daily_data.date',
                'schedules_daily_data.day',
                'schedules_daily_data.time',
                'schedules_daily_data.duration',
                'schedules_daily_data.school',
                'schedules_daily_data.class',
                DB::raw('ad.class_status as raw_class_status'),
                DB::raw('ad.id as assignment_id')
            )
            ->paginate(10)
            ->withQueryString();

        // Add computed fields to each row
        foreach ($dailyData as $data) {
            // Get assignment details for this schedule with tutor names AND class_status
            $assignment = AssignedDailyData::where('schedule_daily_data_id', $data->id)
                ->with([
                    'supervisor:supervisor_id,first_name,last_name',
                    'mainTutor.applicant:applicant_id,first_name,last_name',
                    'mainTutor.account:account_id,account_name',
                    'backupTutor.applicant:applicant_id,first_name,last_name'
                ])
                ->first();
            
            // DIRECTLY use the raw database value from the assignment record
            if ($assignment) {
                $data->setAttribute('raw_class_status', $assignment->class_status);
                $data->setAttribute('assignment_id', $assignment->id);
            } else {
                // Fallback to left join values
                $data->setAttribute('raw_class_status', $data->raw_class_status ?? null);
                $data->setAttribute('assignment_id', $data->assignment_id ?? null);
            }
            
            $hasMainTutor = $assignment && $assignment->main_tutor;
            $hasBackupTutor = $assignment && $assignment->backup_tutor;
            
            $data->total_assigned = ($hasMainTutor ? 1 : 0) + ($hasBackupTutor ? 1 : 0);
            $data->total_required = 2;
            
            // Get supervisor name
            if ($assignment && $assignment->supervisor) {
                $data->setAttribute('assigned_supervisors', trim($assignment->supervisor->first_name . ' ' . $assignment->supervisor->last_name));
                $data->setAttribute('assigned_supervisor_ids', $assignment->assigned_supervisor);
            } else {
                $data->setAttribute('assigned_supervisors', 'None');
                $data->setAttribute('assigned_supervisor_ids', '');
            }
            
            // Get main tutor name and account
            if ($assignment && $assignment->mainTutor) {
                if ($assignment->mainTutor->applicant) {
                    $data->setAttribute('main_tutor_name', trim($assignment->mainTutor->applicant->first_name . ' ' . $assignment->mainTutor->applicant->last_name));
                } else {
                    $data->setAttribute('main_tutor_name', 'Unknown');
                }
                
                // Get account name for display
                if ($assignment->mainTutor->account) {
                    $data->setAttribute('account_name', $assignment->mainTutor->account->account_name);
                } else {
                    $data->setAttribute('account_name', $data->school);
                }
            } else {
                $data->setAttribute('main_tutor_name', 'Not Assigned');
                $data->setAttribute('account_name', $data->school);
            }
            
            // Get backup tutor name
            if ($assignment && $assignment->backupTutor && $assignment->backupTutor->applicant) {
                $data->setAttribute('backup_tutor_name', trim($assignment->backupTutor->applicant->first_name . ' ' . $assignment->backupTutor->applicant->last_name));
            } else {
                $data->setAttribute('backup_tutor_name', 'Not Assigned');
            }
            
            // Add assignment_id for confirmation functionality
            $data->setAttribute('assignment_id', $assignment ? $assignment->id : null);
            
            // Store total counts
            $data->setAttribute('total_assigned', $data->total_assigned);
            $data->setAttribute('total_required', $data->total_required);
            
            // No need to append - setAttribute makes attributes accessible without requiring accessor methods
        }

        // Get available dates and days for filtering
        $availableDates = $this->getAvailableDates($request->input('date'), true);
        $availableDays = $this->getAvailableDays();

        // DEBUG: Log the final data before passing to view
        Log::info('Class list data being passed to view:', [
            'dailyData_count' => $dailyData->count(),
            'sample' => $dailyData->take(5)->map(fn($d) => [
                'id' => $d->id,
                'raw_class_status' => $d->raw_class_status ?? null,
                'assignment_id' => $d->assignment_id ?? null,
            ])->toArray(),
        ]);

        return view('schedules.index', compact('dailyData', 'availableDates', 'availableDays'));
    }

    /**
     * Show employee availability with filtering
     */
    private function showEmployeeAvailability(Request $request)
    {
        // Get all active tutors with applicant info for sorting/display
        $query = Tutor::query()
            ->join('applicants', 'tutors.applicant_id', '=', 'applicants.applicant_id')
            ->where('tutors.status', 'active')
            ->select('tutors.*');
        
        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tutors.username', 'like', '%' . $request->search . '%')
                  ->orWhere('applicants.first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('applicants.last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('tutors.email', 'like', '%' . $request->search . '%')
                  ->orWhere('applicants.phone_number', 'like', '%' . $request->search . '%')
                  ->orWhereRaw("CONCAT(applicants.first_name, ' ', applicants.last_name) LIKE ?", ['%' . $request->search . '%']);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('tutors.status', $request->status);
        }
        
        // Order by applicant name and paginate
        $tutors = $query->orderBy('applicants.first_name')
            ->orderBy('applicants.last_name')
            ->paginate(5)
            ->withQueryString();
        
        // For each tutor, get their GLS account availability information
        $tutors->getCollection()->transform(function ($tutor) use ($request) {
            // Check if tutor's assigned account is GLS
            if ($tutor->account && $tutor->account->account_name === 'GLS') {
                // Get work preference for this tutor (through applicant relationship)
                $workPreference = $tutor->workPreferences;
                
                if ($workPreference && $workPreference->available_times) {
                    // Format available times from work preference
                    $availableTimes = $workPreference->available_times;
                    if (is_array($availableTimes)) {
                        $formatted = [];
                        foreach ($availableTimes as $day => $times) {
                            if (is_array($times)) {
                                $formatted[] = ucfirst($day) . ': ' . implode(', ', $times);
                            } else {
                                $formatted[] = ucfirst($day) . ': ' . $times;
                            }
                        }
                        $tutor->formatted_available_time = implode(' | ', $formatted);
                    } else {
                        $tutor->formatted_available_time = $availableTimes;
                    }
                    $tutor->available_times = $availableTimes;
                } else {
                    $tutor->formatted_available_time = 'No schedule';
                    $tutor->available_times = null;
                }
            } else {
                $tutor->formatted_available_time = 'Not assigned to GLS';
                $tutor->available_times = null;
            }
            
            return $tutor;
        });
        
        // Apply day and time filters by filtering the collection
        if ($request->filled('day') || $request->filled('time_slot')) {
            $tutors->setCollection($tutors->getCollection()->filter(function ($tutor) use ($request) {
                // Check if tutor has available times (from the transform step above)
                if (!$tutor->available_times) {
                    return false;
                }
                
                // Check day filter
                if ($request->filled('day')) {
                    $dayName = $this->normalizeDayName($request->day);
                    $availableTimes = json_encode($tutor->available_times);
                    if (stripos($availableTimes, $dayName) === false) {
                        return false;
                    }
                }
                
                // Check time filter
                if ($request->filled('time_slot')) {
                    $timeSlot = $request->time_slot;
                    $availableTimes = json_encode($tutor->available_times);
                    if (stripos($availableTimes, $timeSlot) === false) {
                        return false;
                    }
                }
                
                return true;
            })->values());
        }
        $availableTimeSlots = $this->getAvailableTimeSlots();
        $availableDays = $this->getAvailableDaysFromTutorAccounts();
        
        // Get all daily data for the calendar view
        $dailyData = DailyData::orderBy('date')
            ->get()
            ->map(function($data) {
                $data->tutor_name = 'TBD';
                // Try to get tutor name from tutor assignments
                if ($data->tutorAssignments && $data->tutorAssignments->count() > 0) {
                    $tutor = $data->tutorAssignments->first()->tutor;
                    if ($tutor && $tutor->applicant) {
                        $data->tutor_name = $tutor->applicant->first_name . ' ' . $tutor->applicant->last_name;
                    }
                }
                return $data;
            });
        
        return view('schedules.index', compact('tutors', 'availableTimeSlots', 'availableDays', 'dailyData'));
    }

    /**
     * Check if a requested time range falls within any of the tutor's available time ranges
     */
    private function isTimeRangeAvailable($availableTimes, $requestedStart, $requestedEnd)
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
            ->with(['tutorAssignments.tutor'])
                ->orderBy('school')
                ->orderBy('time_jst')
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString();

        // Note: Finalization is now tracked in assigned_daily_data, not schedules_daily_data
        $isFinalized = false;
        $finalizedAt = null;

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
            $query->where('account_name', 'GLS')->where('status', 'active');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
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
        // Query fully_assigned schedules from the new table structure - only past dates
        $query = ScheduleDailyData::leftJoin('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
            ->where('assigned_daily_data.class_status', 'fully_assigned')
            ->whereDate('schedules_daily_data.date', '<', now()->toDateString());

        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('schedules_daily_data.date', $request->date);
        }

        if ($request->filled('day')) {
            $day = strtolower($request->day);
            // Map short day names to full names for comparison
            $dayMap = [
                'mon' => 'Monday',
                'tue' => 'Tuesday', 
                'wed' => 'Wednesday',
                'thur' => 'Thursday',
                'thu' => 'Thursday',
                'fri' => 'Friday'
            ];
            
            $dayName = $dayMap[$day] ?? ucfirst($day);
            $query->where('schedules_daily_data.day', $dayName);
        }

        // Get fully assigned schedules with tutor names
        $scheduleHistory = $query->leftJoin('tutor as main_tutor_info', 'assigned_daily_data.main_tutor', '=', 'main_tutor_info.tutor_id')
            ->leftJoin('tutor as backup_tutor_info', 'assigned_daily_data.backup_tutor', '=', 'backup_tutor_info.tutor_id')
            ->select(
                'schedules_daily_data.id',
                'schedules_daily_data.date',
                'schedules_daily_data.day',
                'schedules_daily_data.school',
                'schedules_daily_data.class',
                'schedules_daily_data.time',
                'schedules_daily_data.duration',
                'assigned_daily_data.class_status',
                'assigned_daily_data.finalized_at',
                'assigned_daily_data.main_tutor',
                'assigned_daily_data.backup_tutor',
                DB::raw('CONCAT(main_tutor_info.first_name, " ", main_tutor_info.last_name) as main_tutor_name'),
                DB::raw('CONCAT(backup_tutor_info.first_name, " ", backup_tutor_info.last_name) as backup_tutor_name')
            )
            ->orderBy('schedules_daily_data.date', 'desc')
            ->orderBy('schedules_daily_data.time', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Get available dates for filtering (only from fully_assigned schedules)
        $availableDates = ScheduleDailyData::leftJoin('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
            ->where('assigned_daily_data.class_status', 'fully_assigned')
            ->select('schedules_daily_data.date')
            ->distinct()
            ->orderBy('schedules_daily_data.date', 'desc')
            ->pluck('schedules_daily_data.date');

        // Get available days for filtering (only from fully_assigned schedules)
        $availableDays = ScheduleDailyData::leftJoin('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
            ->where('assigned_daily_data.class_status', 'fully_assigned')
            ->select('schedules_daily_data.day')
            ->distinct()
            ->orderBy('schedules_daily_data.day')
            ->pluck('schedules_daily_data.day');

        return view('schedules.index', compact('scheduleHistory', 'availableDates', 'availableDays'));
    }

    /**
     * Search schedules
     */
    public function searchSchedules(Request $request)
    {
        try {
            // Per-row schedules with status join for consistent display
            $query = ScheduleDailyData::query()
                ->leftJoin('assigned_daily_data as ad', 'ad.schedule_daily_data_id', '=', 'schedules_daily_data.id');

            // Apply search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('schedules_daily_data.school', 'like', '%' . $searchTerm . '%')
                      ->orWhere('schedules_daily_data.class', 'like', '%' . $searchTerm . '%');
                });
            }

            // Date or Day filter
            $dateFilter = $request->input('date');
            $dayFilter = $request->input('day');

            if ($dateFilter && trim($dateFilter) !== '') {
                $query->whereDate('schedules_daily_data.date', $dateFilter);
            } elseif ($dayFilter && trim($dayFilter) !== '') {
                $day = $this->validateDayName($dayFilter);
                if ($day) {
                    $dayOfWeek = $this->getDayOfWeek($day);
                    if ($dayOfWeek) {
                        $query->whereRaw('DAYOFWEEK(schedules_daily_data.date) = ?', [$dayOfWeek]);
                    }
                }
            }

            // Status filter via joined table
            if ($request->filled('status')) {
                $status = $request->input('status');
                if ($status === 'not_assigned') {
                    $query->where(function($q) {
                        $q->whereNull('ad.id')
                          ->orWhere('ad.class_status', 'not_assigned');
                    });
                } else {
                    $query->where('ad.class_status', $status);
                }
            }

            // Apply sorting
            $sortField = $request->input('sort');
            $sortDirection = $request->input('direction', 'asc');
            
            // Validate sort field and direction
            $allowedSorts = ['date', 'day', 'time', 'school', 'status'];
            
            // Only apply custom sorting if sort field is provided and valid
            if ($sortField && in_array($sortField, $allowedSorts)) {
                $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
                
                // Map status sort to the actual column
                if ($sortField === 'status') {
                    $query->orderBy(DB::raw('ad.class_status'), $sortDirection);
                } elseif ($sortField === 'day') {
                    $query->orderBy(DB::raw('DAYOFWEEK(schedules_daily_data.date)'), $sortDirection);
                } else {
                    $query->orderBy('schedules_daily_data.' . $sortField, $sortDirection);
                }
                
                // Add secondary sort by time if not sorting by time
                if ($sortField !== 'time') {
                    $query->orderBy('schedules_daily_data.time', 'asc');
                }
            } else {
                // Default sorting when no sort parameter or after reset
                $query->orderBy('schedules_daily_data.date', 'asc');
                $query->orderBy('schedules_daily_data.time', 'asc');
            }
            
            // Select fields including joined status
            $dailyData = $query->select(
                    'schedules_daily_data.id',
                    'schedules_daily_data.date',
                    DB::raw('DAYNAME(schedules_daily_data.date) as day'),
                    'schedules_daily_data.time',
                    'schedules_daily_data.duration',
                    'schedules_daily_data.school',
                    'schedules_daily_data.class',
                    DB::raw('ad.class_status as raw_class_status'),
                    DB::raw('ad.id as assignment_id')
                )
                ->paginate(10)
                ->withQueryString();

            // Populate display attributes
            foreach ($dailyData as $data) {
                $assignment = AssignedDailyData::where('schedule_daily_data_id', $data->id)
                    ->with([
                        'supervisor:supervisor_id,first_name,last_name',
                        'mainTutor.applicant:applicant_id,first_name,last_name',
                        'mainTutor.account:account_id,account_name',
                        'backupTutor.applicant:applicant_id,first_name,last_name'
                    ])->first();

                if ($assignment) {
                    $data->setAttribute('raw_class_status', $assignment->class_status);
                    $data->setAttribute('assignment_id', $assignment->id);
                } else {
                    $data->setAttribute('raw_class_status', $data->raw_class_status ?? null);
                    $data->setAttribute('assignment_id', $data->assignment_id ?? null);
                }

                $hasMainTutor = $assignment && $assignment->main_tutor;
                $hasBackupTutor = $assignment && $assignment->backup_tutor;
                $data->total_assigned = ($hasMainTutor ? 1 : 0) + ($hasBackupTutor ? 1 : 0);
                $data->total_required = 2;

                if ($assignment && $assignment->supervisor) {
                    $data->setAttribute('assigned_supervisors', trim($assignment->supervisor->first_name . ' ' . $assignment->supervisor->last_name));
                    $data->setAttribute('assigned_supervisor_ids', $assignment->assigned_supervisor);
                } else {
                    $data->setAttribute('assigned_supervisors', 'None');
                    $data->setAttribute('assigned_supervisor_ids', '');
                }

                if ($assignment && $assignment->mainTutor) {
                    if ($assignment->mainTutor->applicant) {
                        $data->setAttribute('main_tutor_name', trim($assignment->mainTutor->applicant->first_name . ' ' . $assignment->mainTutor->applicant->last_name));
                    } else {
                        $data->setAttribute('main_tutor_name', 'Unknown');
                    }

                    if ($assignment->mainTutor->account) {
                        $data->setAttribute('account_name', $assignment->mainTutor->account->account_name);
                    } else {
                        $data->setAttribute('account_name', $data->school);
                    }
                } else {
                    $data->setAttribute('main_tutor_name', 'Not Assigned');
                    $data->setAttribute('account_name', $data->school);
                }

                if ($assignment && $assignment->backupTutor && $assignment->backupTutor->applicant) {
                    $data->setAttribute('backup_tutor_name', trim($assignment->backupTutor->applicant->first_name . ' ' . $assignment->backupTutor->applicant->last_name));
                } else {
                    $data->setAttribute('backup_tutor_name', 'Not Assigned');
                }

                $data->setAttribute('total_assigned', $data->total_assigned);
                $data->setAttribute('total_required', $data->total_required);
                // No need to append - setAttribute makes attributes accessible without requiring accessor methods
            }

            $availableDates = $this->getAvailableDates($request->input('date'), true);
            $availableDays = $this->getAvailableDays();

            Log::info('SearchSchedules joined data:', [
                'count' => $dailyData->count(),
                'sample' => $dailyData->take(5)->map(fn($d) => ['id' => $d->id, 'status' => $d->raw_class_status])->toArray(),
            ]);

            if ($request->ajax()) {
                $html = view('schedules.partials.class-schedule-table', ['schedules' => $dailyData])->render();
                $pagination = view('schedules.partials.class-pagination', ['dailyData' => $dailyData])->render();

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'total' => $dailyData->total()
                ]);
            }

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
                    // Note: schedules_daily_data no longer has schedule_status, finalized_at, or finalized_by
                    // These are now tracked in assigned_daily_data table
                    $oldData = [];

                $updateData = [];
                // No updates needed for schedules_daily_data - it only stores schedule info
                // Finalization is handled through assigned_daily_data

                // $class->update($updateData);

                if (method_exists($class, 'createHistoryRecord')) {
                    $class->createHistoryRecord(
                        $status === 'final' ? 'finalized' : 'updated',
                        $performedBy,
                        "Schedule saved as " . ($status === 'final' ? 'finalized' : 'tentative'),
                        $oldData,
                        []
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
            $tutor->update(['status' => $newStatus]);

            $tutorName = $tutor->full_name ?? 'Tutor';
            $actionText = $newStatus === 'active' ? 'activated and is now available for class assignments' : 'deactivated and will no longer receive class assignments';
            
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
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone_number', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('applicant', function($appQ) use ($searchTerm) {
                          $appQ->where('first_name', 'like', '%' . $searchTerm . '%')
                               ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                               ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchTerm . '%']);
                      });
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
            
            $tutors = $query->leftJoin('applicants', 'tutors.applicant_id', '=', 'applicants.applicant_id')
                            ->select('tutors.*')
                            ->orderBy('applicants.first_name')
                            ->orderBy('applicants.last_name')
                            ->paginate(5)->withQueryString();
            
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
        // Use ScheduleDailyData table directly - no schedule_status column exists
        $query = ScheduleDailyData::select('date')->distinct();
        
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
        // Use ScheduleDailyData table - no schedule_status filtering needed
        $dates = ScheduleDailyData::select('date')
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
        // Get all active tutors with GLS account and their work preferences
        $workPreferences = \App\Models\WorkPreference::join('applicants', 'work_preferences.applicant_id', '=', 'applicants.applicant_id')
            ->join('tutors', 'tutors.applicant_id', '=', 'applicants.applicant_id')
            ->join('accounts', 'tutors.account_id', '=', 'accounts.account_id')
            ->where('accounts.account_name', 'GLS')
            ->whereNotNull('work_preferences.days_available')
            ->select('work_preferences.days_available')
            ->distinct()
            ->get();
        
        $allDays = collect();
        
        foreach ($workPreferences as $preference) {
            $availableDays = $preference->days_available;
            
            // Handle case where days_available might be a string instead of array
            if (is_string($availableDays)) {
                $availableDays = json_decode($availableDays, true) ?? [];
            }
            
            if (is_array($availableDays)) {
                $allDays = $allDays->merge($availableDays);
            }
        }
        
        // Get unique days and filter out empty values and weekdays only
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $uniqueDays = $allDays->filter()->unique()->filter(function($day) use ($weekdays) {
            return in_array($day, $weekdays);
        })->values();
        
        // Sort days in chronological order
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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
     * Get available tutors for a given date, time, and supervisor's account
     */
    public function getAvailableTutors(Request $request)
    {
        try {
            $date = $request->input('date');
            $time = $request->input('time');
            $supervisorAccount = $request->input('account');
            $normalizedAccount = $supervisorAccount ? strtolower($supervisorAccount) : null;
            $excludeTimeConflicts = $request->input('exclude_time_conflicts', false);
            
            if (!$supervisorAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is required'
                ], 400);
            }
            
            // Get day of week from date if provided
            $dayOfWeek = $date ? Carbon::parse($date)->format('l') : null;
            
            // Get tutors already assigned at this time on this date (to exclude them)
            $assignedTutorIdsAtTime = [];
            if ($excludeTimeConflicts && $date && $time) {
                $assignedTutorIdsAtTime = AssignedDailyData::join('schedules_daily_data', 'assigned_daily_data.schedule_daily_data_id', '=', 'schedules_daily_data.id')
                    ->where('schedules_daily_data.date', $date)
                    ->where('schedules_daily_data.time', $time)
                    ->whereIn('assigned_daily_data.class_status', ['partially_assigned', 'fully_assigned'])
                    ->get()
                    ->flatMap(function($assignment) {
                        $ids = [];
                        if ($assignment->main_tutor) $ids[] = $assignment->main_tutor;
                        if ($assignment->backup_tutor) $ids[] = $assignment->backup_tutor;
                        return $ids;
                    })
                    ->unique()
                    ->toArray();
            }
            
            // Get ALL active tutors from the supervisor's account with their work preferences
            $availableTutors = Tutor::where('tutors.status', 'active')
                ->join('accounts', 'tutors.account_id', '=', 'accounts.account_id')
                ->join('applicants', 'tutors.applicant_id', '=', 'applicants.applicant_id')
                ->leftJoin('work_preferences', 'applicants.applicant_id', '=', 'work_preferences.applicant_id')
                ->when($normalizedAccount, function ($query) use ($normalizedAccount) {
                    // Use case-insensitive match so seeds using lowercase account names still work
                    $query->whereRaw('LOWER(accounts.account_name) = ?', [$normalizedAccount]);
                })
                ->select(
                    'tutors.tutor_id',
                    'tutors.tutorID',
                    'tutors.applicant_id',
                    'applicants.first_name',
                    'applicants.last_name',
                    'accounts.account_name',
                    'work_preferences.start_time',
                    'work_preferences.end_time',
                    'work_preferences.days_available'
                )
                ->orderBy('tutors.tutorID')
                ->get();
            
            // Debug: Log the raw query results
            Log::info('Available tutors raw query result', [
                'count' => $availableTutors->count(),
                'first_tutor' => $availableTutors->first()
            ]);
            
            $availableTutors = $availableTutors
                ->filter(function($tutor) use ($assignedTutorIdsAtTime) {
                    // Exclude tutors already assigned at this time
                    return !in_array($tutor->tutor_id, $assignedTutorIdsAtTime);
                })
                ->map(function($tutor) use ($dayOfWeek, $time) {
                    // Format availability display
                    $availability = 'Available';
                    if ($tutor->start_time && $tutor->end_time) {
                        $availability = Carbon::parse($tutor->start_time)->format('g:i A') . ' - ' . Carbon::parse($tutor->end_time)->format('g:i A');
                    }
                    
                    // Check if days_available matches (if we have a day to check)
                    $matchesDay = true;
                    if ($dayOfWeek && $tutor->days_available) {
                        $daysArray = is_array($tutor->days_available) ? $tutor->days_available : json_decode($tutor->days_available, true);
                        $matchesDay = is_array($daysArray) && in_array($dayOfWeek, $daysArray);
                    }
                    
                    // Check if time matches (if we have a time to check)
                    $matchesTime = true;
                    if ($time && $tutor->start_time && $tutor->end_time) {
                        $matchesTime = $time >= $tutor->start_time && $time <= $tutor->end_time;
                    }
                    
                    // Add indicator if doesn't match filters (but still show them)
                    $availabilityNote = '';
                    if (!$matchesDay || !$matchesTime) {
                        $availabilityNote = ' (outside preferred schedule)';
                    }
                    
                    // Construct full name with fallback - remove all whitespace characters and rebuild
                    $firstName = preg_replace('/\s+/', ' ', trim($tutor->first_name ?? ''));
                    $lastName = preg_replace('/\s+/', ' ', trim($tutor->last_name ?? ''));
                    $fullName = trim($firstName . ' ' . $lastName);
                    if (empty($fullName)) {
                        $fullName = 'Name Not Set';
                    }
                    
                    // Debug log if name is empty
                    if (empty(trim($tutor->first_name . ' ' . $tutor->last_name))) {
                        Log::warning('Tutor missing name', [
                            'tutor_id' => $tutor->tutor_id,
                            'tutorID' => $tutor->tutorID,
                            'applicant_id' => $tutor->applicant_id,
                            'first_name' => $tutor->first_name,
                            'last_name' => $tutor->last_name
                        ]);
                    }
                    
                    $result = [
                        'id' => $tutor->tutor_id,
                        'tutorID' => $tutor->tutorID,
                        'name' => $fullName,
                        'account' => $tutor->account_name,
                        'availability' => $availability . $availabilityNote
                    ];
                    
                    // Debug: Log what we're returning
                    Log::info('Mapped tutor data', $result);
                    
                    return $result;
                })
                ->values(); // Re-index array after filter
            
            return response()->json([
                'success' => true,
                'tutors' => $availableTutors,
                'count' => $availableTutors->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching available tutors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tutors: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Assign a tutor and supervisor to a schedule
     */
    public function assignTutorToSchedule(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'school' => 'required|string',
                'main_tutor_id' => 'required|exists:tutors,tutor_id',
                'backup_tutor_id' => 'nullable|exists:tutors,tutor_id',
                'notes' => 'nullable|string',
                'time' => 'nullable|string'
            ]);
            
            // Ensure main and backup tutors are not the same
            if (!empty($validated['backup_tutor_id']) && $validated['main_tutor_id'] == $validated['backup_tutor_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Main tutor and backup tutor cannot be the same'
                ], 422);
            }
            
            // Get the logged-in supervisor
            $supervisor = Auth::guard('supervisor')->user();
            
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supervisor not authenticated'
                ], 401);
            }
            
            // Find the schedule(s) for this date and school
            $query = ScheduleDailyData::where('date', $validated['date'])
                ->where('school', $validated['school']);
            
            // If time is provided, filter by time
            if (!empty($validated['time'])) {
                $query->where('time', $validated['time']);
            }
            
            $schedules = $query->get();
            
            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found for the specified criteria'
                ], 404);
            }
            
            // Assign tutor and supervisor to all matching schedules
            $assigned = 0;
            $mainTutor = Tutor::find($validated['main_tutor_id']);
            $backupTutor = !empty($validated['backup_tutor_id']) ? Tutor::find($validated['backup_tutor_id']) : null;
            
            foreach ($schedules as $schedule) {
                // Get or create assigned_daily_data record
                $assignment = AssignedDailyData::firstOrCreate(
                    ['schedule_daily_data_id' => $schedule->id],
                    ['class_status' => 'not_assigned']
                );
                
                // When tutors are assigned, status is pending_acceptance (waiting for tutor confirmation)
                // Status will become fully_assigned when tutor accepts
                
                // Update with tutors and supervisor
                $assignment->update([
                    'main_tutor' => $validated['main_tutor_id'],
                    'backup_tutor' => $validated['backup_tutor_id'] ?? null,
                    'assigned_supervisor' => $supervisor->supervisor_id,
                    'class_status' => 'pending_acceptance',
                    'notes' => $validated['notes'] ?? $assignment->notes
                ]);
                
                // Create notification for main tutor
                if ($mainTutor) {
                    Notification::create([
                        'user_id' => $mainTutor->tutor_id,
                        'user_type' => 'tutor',
                        'type' => 'assignment_request',
                        'title' => 'New Class Assignment',
                        'message' => "You have been assigned to {$schedule->school} - {$schedule->class} on {$schedule->date} at {$schedule->time} by {$supervisor->full_name}. Please accept or decline.",
                        'icon' => 'fas fa-chalkboard-teacher',
                        'color' => 'blue',
                        'is_read' => false,
                        'data' => [
                            'assignment_id' => $assignment->id,
                            'schedule_id' => $schedule->id,
                            'school' => $schedule->school,
                            'class' => $schedule->class,
                            'date' => $schedule->date,
                            'time' => $schedule->time,
                            'role' => 'main',
                            'supervisor_id' => $supervisor->supervisor_id
                        ]
                    ]);
                }
                
                // Create notification for backup tutor if assigned
                if ($backupTutor) {
                    Notification::create([
                        'user_id' => $backupTutor->tutor_id,
                        'user_type' => 'tutor',
                        'type' => 'assignment_request',
                        'title' => 'New Class Assignment (Backup)',
                        'message' => "You have been assigned as backup tutor to {$schedule->school} - {$schedule->class} on {$schedule->date} at {$schedule->time} by {$supervisor->full_name}. Please accept or decline.",
                        'icon' => 'fas fa-chalkboard-teacher',
                        'color' => 'blue',
                        'is_read' => false,
                        'data' => [
                            'assignment_id' => $assignment->id,
                            'schedule_id' => $schedule->id,
                            'school' => $schedule->school,
                            'class' => $schedule->class,
                            'date' => $schedule->date,
                            'time' => $schedule->time,
                            'role' => 'backup',
                            'supervisor_id' => $supervisor->supervisor_id
                        ]
                    ]);
                }
                
                $assigned++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully assigned tutor(s) to {$assigned} schedule(s)",
                'assigned_count' => $assigned
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error assigning tutor to schedule', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error assigning tutor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Confirm assignment (change status from partially_assigned to fully_assigned)
     */
    public function confirmAssignment(Request $request)
    {
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|exists:assigned_daily_data,id'
            ]);
            
            // Get the logged-in supervisor
            $supervisor = Auth::guard('supervisor')->user();
            
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supervisor not authenticated'
                ], 401);
            }
            
            // Get the assignment
            $assignment = AssignedDailyData::find($validated['assignment_id']);
            
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ], 404);
            }
            
            // Check if the supervisor is the one who assigned this
            if ($assignment->assigned_supervisor != $supervisor->supervisor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to confirm this assignment'
                ], 403);
            }
            
            // Confirm the assignment
            $assignment->update([
                'class_status' => 'fully_assigned',
                'finalized_at' => now(),
                'finalized_by' => $supervisor->supervisor_id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment confirmed successfully!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error confirming assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error confirming assignment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Finalize schedule - mark as fully_assigned and notify tutors
     */
    public function finalizeSchedule(Request $request)
    {
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|exists:assigned_daily_data,id',
                'schedule_id' => 'required|exists:schedules_daily_data,id'
            ]);
            
            // Get the logged-in supervisor
            $supervisor = Auth::guard('supervisor')->user();
            
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supervisor not authenticated'
                ], 401);
            }
            
            // Get the assignment and schedule
            $assignment = AssignedDailyData::find($validated['assignment_id']);
            $schedule = ScheduleDailyData::find($validated['schedule_id']);
            
            if (!$assignment || !$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment or schedule not found'
                ], 404);
            }
            
            // Check if already finalized
            if ($assignment->class_status === 'fully_assigned') {
                return response()->json([
                    'success' => false,
                    'message' => 'This schedule is already fully assigned'
                ], 422);
            }
            
            // Finalize the assignment
            $assignment->update([
                'class_status' => 'fully_assigned',
                'finalized_at' => now(),
                'finalized_by' => $supervisor->supervisor_id
            ]);
            
            // Create notifications for assigned tutors
            $notificationsSent = 0;
            
            if ($assignment->main_tutor) {
                Notification::create([
                    'user_id' => $assignment->main_tutor,
                    'user_type' => 'tutor',
                    'title' => 'New Class Assignment',
                    'message' => "You have been assigned to teach at {$schedule->school} on " . 
                                \Carbon\Carbon::parse($schedule->date)->format('F j, Y') . 
                                " at " . \Carbon\Carbon::parse($schedule->time)->format('g:i A') . 
                                " (Main Tutor)",
                    'type' => 'assignment',
                    'is_read' => false
                ]);
                $notificationsSent++;
            }
            
            if ($assignment->backup_tutor) {
                Notification::create([
                    'user_id' => $assignment->backup_tutor,
                    'user_type' => 'tutor',
                    'title' => 'New Class Assignment',
                    'message' => "You have been assigned to teach at {$schedule->school} on " . 
                                \Carbon\Carbon::parse($schedule->date)->format('F j, Y') . 
                                " at " . \Carbon\Carbon::parse($schedule->time)->format('g:i A') . 
                                " (Backup Tutor)",
                    'type' => 'assignment',
                    'is_read' => false
                ]);
                $notificationsSent++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Schedule finalized successfully! {$notificationsSent} tutor(s) have been notified."
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error finalizing schedule', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error finalizing schedule: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Tutor accepts assignment
     */
    public function acceptAssignment(Request $request)
    {
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|exists:assigned_daily_data,id'
            ]);
            
            // Get the logged-in tutor
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }
            
            // Get the assignment
            $assignment = AssignedDailyData::with('schedule')->find($validated['assignment_id']);
            
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ], 404);
            }
            
            // Check if tutor is assigned to this schedule
            if ($assignment->main_tutor != $tutor->tutor_id && $assignment->backup_tutor != $tutor->tutor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this schedule'
                ], 403);
            }
            
            // Check if assignment is pending acceptance
            if ($assignment->class_status !== 'pending_acceptance') {
                return response()->json([
                    'success' => false,
                    'message' => 'This assignment is not pending acceptance'
                ], 422);
            }
            
            // Mark as fully assigned
            $assignment->update([
                'class_status' => 'fully_assigned',
                'finalized_at' => now()
            ]);
            
            // Get schedule details
            $schedule = $assignment->schedule;
            $supervisor = Supervisor::find($assignment->assigned_supervisor);
            
            // Notify supervisor of acceptance
            if ($supervisor) {
                Notification::create([
                    'user_id' => $supervisor->supervisor_id,
                    'user_type' => 'supervisor',
                    'type' => 'assignment_accepted',
                    'title' => 'Assignment Accepted',
                    'message' => "{$tutor->full_name} has accepted the assignment for {$schedule->school} - {$schedule->class} on {$schedule->date} at {$schedule->time}.",
                    'icon' => 'fas fa-check-circle',
                    'color' => 'green',
                    'is_read' => false,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'schedule_id' => $schedule->id,
                        'tutor_id' => $tutor->tutor_id,
                        'tutor_name' => $tutor->full_name
                    ]
                ]);
            }
            
            // Mark the notification as read
            Notification::where('user_id', $tutor->tutor_id)
                ->where('user_type', 'tutor')
                ->where('type', 'assignment_request')
                ->whereJsonContains('data->assignment_id', $assignment->id)
                ->update(['is_read' => true, 'read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment accepted successfully! The supervisor has been notified.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error accepting assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error accepting assignment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Tutor rejects assignment
     */
    public function rejectAssignment(Request $request)
    {
        try {
            $validated = $request->validate([
                'assignment_id' => 'required|exists:assigned_daily_data,id',
                'reason' => 'nullable|string|max:500'
            ]);
            
            // Get the logged-in tutor
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }
            
            // Get the assignment
            $assignment = AssignedDailyData::with('schedule')->find($validated['assignment_id']);
            
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment not found'
                ], 404);
            }
            
            // Check if tutor is assigned to this schedule
            $isMain = $assignment->main_tutor == $tutor->tutor_id;
            $isBackup = $assignment->backup_tutor == $tutor->tutor_id;
            
            if (!$isMain && !$isBackup) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this schedule'
                ], 403);
            }
            
            // Check if assignment is pending acceptance
            if ($assignment->class_status !== 'pending_acceptance') {
                return response()->json([
                    'success' => false,
                    'message' => 'This assignment is not pending acceptance'
                ], 422);
            }
            
            // Remove tutor from assignment
            if ($isMain) {
                $assignment->main_tutor = null;
            }
            if ($isBackup) {
                $assignment->backup_tutor = null;
            }
            
            // If both tutors are now null, set status back to not_assigned
            if (!$assignment->main_tutor && !$assignment->backup_tutor) {
                $assignment->class_status = 'not_assigned';
            }
            
            $assignment->save();
            
            // Get schedule details
            $schedule = $assignment->schedule;
            $supervisor = Supervisor::find($assignment->assigned_supervisor);
            $reason = $validated['reason'] ?? 'No reason provided';
            
            // Notify supervisor of rejection
            if ($supervisor) {
                Notification::create([
                    'user_id' => $supervisor->supervisor_id,
                    'user_type' => 'supervisor',
                    'type' => 'assignment_rejected',
                    'title' => 'Assignment Rejected',
                    'message' => "{$tutor->full_name} has declined the assignment for {$schedule->school} - {$schedule->class} on {$schedule->date} at {$schedule->time}. Reason: {$reason}",
                    'icon' => 'fas fa-times-circle',
                    'color' => 'red',
                    'is_read' => false,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'schedule_id' => $schedule->id,
                        'tutor_id' => $tutor->tutor_id,
                        'tutor_name' => $tutor->full_name,
                        'reason' => $reason
                    ]
                ]);
            }
            
            // Mark the notification as read
            Notification::where('user_id', $tutor->tutor_id)
                ->where('user_type', 'tutor')
                ->where('type', 'assignment_request')
                ->whereJsonContains('data->assignment_id', $assignment->id)
                ->update(['is_read' => true, 'read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assignment declined. The supervisor has been notified.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error rejecting assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
