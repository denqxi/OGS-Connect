<?php

namespace App\Services;

use App\Models\Tutor;
use App\Models\DailyData;
use App\Models\TutorAssignment;
use App\Models\TutorAccount;
use App\Models\Availability;
use App\Models\TimeSlot;
use App\Models\ScheduleHistory;
use App\Models\Supervisor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TutorAssignmentService
{
    /**
     * Auto-assign tutors using enhanced availability system with account support
     */
    public function autoAssignTutors($date = null, $day = null, $accountName = 'GLS')
    {
        Log::info('Starting auto-assignment', ['date' => $date, 'day' => $day, 'account' => $accountName]);
        
        $query = DailyData::query();
        
        if ($date) {
            $query->whereDate('date', $date);
        }
        
        if ($day) {
            // Validate and sanitize day name to prevent SQL injection
            $dayName = $this->validateDayName($day);
            if ($dayName) {
                $query->whereRaw('DAYNAME(date) = ?', [$dayName]);
            }
        }

        // Get current supervisor ID
        $currentSupervisorId = null;
        if (Auth::guard('supervisor')->check()) {
            $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
        } elseif (session('supervisor_id')) {
            $currentSupervisorId = session('supervisor_id');
        }

        // Get classes that need more tutors and can be assigned by current supervisor
        $classes = $query->get()->filter(function($class) use ($currentSupervisorId) {
            $assignedCount = $class->tutorAssignments()->count();
            $needsMoreTutors = $assignedCount < $class->number_required;
            
            // Check if ANY class in the same schedule (same date) is owned by another supervisor
            $scheduleDate = $class->date;
            $existingOwner = DailyData::where('date', $scheduleDate)
                ->whereNotNull('assigned_supervisor')
                ->where('assigned_supervisor', '!=', $currentSupervisorId)
                ->first();
            
            $canBeAssigned = !$existingOwner; // Can be assigned if no other supervisor owns the schedule
            
            return $needsMoreTutors && $canBeAssigned;
        });

        Log::info('Classes needing tutors', ['count' => $classes->count()]);

        $assignedCount = 0;
        $results = [];

        foreach ($classes as $class) {
            $currentAssigned = $class->tutorAssignments()->count();
            $needMore = $class->number_required - $currentAssigned;
            
            Log::info('Processing class', [
                'id' => $class->id,
                'school' => $class->school,
                'class' => $class->class,
                'date' => $class->date,
                'time_jst' => $class->time_jst,
                'current_assigned' => $currentAssigned,
                'need_more' => $needMore
            ]);
            
            if ($needMore > 0) {
                $bestTutors = $this->findBestTutorsForClass($class, $needMore, $accountName);
                
                Log::info('Found potential tutors', ['count' => count($bestTutors)]);
                
                foreach ($bestTutors as $tutorMatch) {
                    // Check if tutor is not already assigned to this class
                    $existingAssignment = TutorAssignment::where('daily_data_id', $class->id)
                        ->where('tutor_id', $tutorMatch['tutor']->tutorID)
                        ->first();
                        
                    if (!$existingAssignment) {
                        TutorAssignment::create([
                            'daily_data_id' => $class->id,
                            'tutor_id' => $tutorMatch['tutor']->tutorID,
                            'assigned_at' => now(),
                        ]);
                        
                        $assignedCount++;
                        $results[] = [
                            'class_id' => $class->id,
                            'tutor_id' => $tutorMatch['tutor']->tutorID,
                            'tutor_name' => $tutorMatch['tutor']->tusername,
                            'similarity_score' => $tutorMatch['similarity'],
                            'school' => $class->school,
                            'class_name' => $class->class,
                            'date' => $class->date,
                            'time' => $class->time_jst
                        ];
                        
                        Log::info('Assigned tutor to class', [
                            'tutor' => $tutorMatch['tutor']->tusername,
                            'class' => $class->class,
                            'similarity' => $tutorMatch['similarity']
                        ]);
                    }
                }
            }
        }

        // Create history records for classes that had tutors assigned
        if ($assignedCount > 0) {
            $this->createAssignmentHistoryRecords($results);
        }

        Log::info('Auto-assignment completed', ['total_assigned' => $assignedCount]);

        return [
            'assigned' => $assignedCount,
            'assignments' => $results
        ];
    }

    /**
     * Find best tutors for a class using new availability system with account support
     */
    private function findBestTutorsForClass($class, $limit = 10, $accountName = 'GLS')
    {
        // Get all active tutors who have accounts for the specified account name
        $availableTutors = Tutor::where('status', 'active')
            ->whereHas('accounts', function($query) use ($accountName) {
                $query->forAccount($accountName)->active();
            })
            ->with(['accounts' => function($query) use ($accountName) {
                $query->forAccount($accountName)->active();
            }])
            ->get();

        $classDayName = Carbon::parse($class->date)->format('l'); // Full day name (Monday, Tuesday, etc.)
        $classTime = Carbon::parse($class->time_jst);
        
        Log::info('Finding tutors for class', [
            'class_day' => $classDayName,
            'class_time' => $classTime->format('H:i:s'),
            'account' => $accountName,
            'total_tutors' => $availableTutors->count()
        ]);

        $tutorSimilarities = [];

        foreach ($availableTutors as $tutor) {
            // Get the tutor's account for this specific account name
            $tutorAccount = $tutor->accounts->first(); // Should only be one account per accountName due to unique constraint
            
            if (!$tutorAccount) {
                Log::debug('Tutor has no account for this account name', ['tutor' => $tutor->tusername, 'account' => $accountName]);
                continue;
            }

            // Check if tutor is available using new account-specific system
            if ($tutorAccount->available_days && $tutorAccount->available_times) {
                // Handle case where available_days might be a string instead of array
                $availableDays = $tutorAccount->available_days;
                if (is_string($availableDays)) {
                    $availableDays = json_decode($availableDays, true) ?? [];
                }
                
                // Handle case where available_times might be a string instead of array
                $availableTimes = $tutorAccount->available_times;
                if (is_string($availableTimes)) {
                    $availableTimes = json_decode($availableTimes, true) ?? [];
                }
                
                // Skip if not available on this day
                if (!in_array($classDayName, $availableDays)) {
                    Log::debug('Tutor not available on this day (account system)', ['tutor' => $tutor->tusername, 'day' => $classDayName, 'account' => $accountName]);
                    continue;
                }
                
                // Get time ranges for this day
                $dayTimes = $availableTimes[$classDayName] ?? [];
                if (empty($dayTimes)) {
                    Log::debug('Tutor has no time slots for this day', ['tutor' => $tutor->tusername, 'day' => $classDayName, 'account' => $accountName]);
                    continue;
                }
                
                // Calculate best similarity for this day
                $maxSimilarity = $this->calculateAccountSystemSimilarity($classTime, $dayTimes, $tutorAccount, $classDayName);
                
            } else {
                // Fallback to old availability system if account has no new data
                Log::debug('Using old availability system for tutor account', ['tutor' => $tutor->tusername, 'account' => $accountName]);
                
                // Check if tutor has old availability data
                if ($tutor->available_days && $tutor->available_times) {
                    $availableDays = is_string($tutor->available_days) ? json_decode($tutor->available_days, true) : $tutor->available_days;
                    $availableTimes = is_string($tutor->available_times) ? json_decode($tutor->available_times, true) : $tutor->available_times;
                    
                    if (!in_array($classDayName, $availableDays)) {
                        continue;
                    }
                    
                    $dayTimes = $availableTimes[$classDayName] ?? [];
                    if (empty($dayTimes)) {
                        continue;
                    }
                    
                    $maxSimilarity = $this->calculateNewSystemSimilarity($classTime, $dayTimes, $tutor, $classDayName);
                } else {
                    // No availability data found - skip this tutor
                    continue;
                }
            }

            // Only consider tutors with good similarity (> 0.5 for stricter matching)
            if ($maxSimilarity > 0.5) {
                $tutorSimilarities[] = [
                    'tutor' => $tutor,
                    'similarity' => $maxSimilarity
                ];
                
                Log::debug('Tutor similarity calculated', [
                    'tutor' => $tutor->tusername,
                    'similarity' => $maxSimilarity
                ]);
            } else {
                Log::debug('Tutor rejected due to low similarity', [
                    'tutor' => $tutor->tusername,
                    'similarity' => $maxSimilarity,
                    'threshold' => 0.5
                ]);
            }
        }

        // Sort by similarity score (highest first) and return top matches
        usort($tutorSimilarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        $topMatches = array_slice($tutorSimilarities, 0, $limit);
        
        Log::info('Top tutor matches found', [
            'count' => count($topMatches),
            'matches' => array_map(function($match) {
                return [
                    'tutor' => $match['tutor']->tusername,
                    'similarity' => round($match['similarity'], 3)
                ];
            }, $topMatches)
        ]);

        return $topMatches;
    }

    /**
     * Calculate similarity based on time overlap
     */
    private function calculateTimeOverlapSimilarity($classTime, $timeSlot)
    {
        $classMinutes = $classTime->hour * 60 + $classTime->minute;
        
        $slotStart = Carbon::parse($timeSlot->startTime);
        $slotEnd = Carbon::parse($timeSlot->endTime);
        $slotStartMinutes = $slotStart->hour * 60 + $slotStart->minute;
        $slotEndMinutes = $slotEnd->hour * 60 + $slotEnd->minute;
        
        // If class time falls within the available time slot, high similarity
        if ($classMinutes >= $slotStartMinutes && $classMinutes <= $slotEndMinutes) {
            return 1.0; // Perfect match
        }
        
        // Calculate distance-based similarity (closer times get higher scores)
        $distance = min(
            abs($classMinutes - $slotStartMinutes),
            abs($classMinutes - $slotEndMinutes)
        );
        
        // Convert distance to similarity (max 4 hours = 240 minutes for reasonable similarity)
        $maxDistance = 240; // 4 hours
        $similarity = max(0, 1 - ($distance / $maxDistance));
        
        return $similarity;
    }

    /**
     * Calculate similarity using new availability system
     */
    private function calculateNewSystemSimilarity($classTime, $dayTimes, $tutor, $classDayName)
    {
        $classMinutes = $classTime->hour * 60 + $classTime->minute;
        $maxSimilarity = 0;
        
        Log::debug('Calculating new system similarity', [
            'tutor' => $tutor->tusername,
            'class_time' => $classTime->format('H:i'),
            'class_minutes' => $classMinutes,
            'day_times' => $dayTimes
        ]);
        
        foreach ($dayTimes as $timeRange) {
            if (strpos($timeRange, '-') !== false) {
                [$startTime, $endTime] = explode('-', $timeRange);
                
                $startMinutes = $this->timeToMinutes($startTime);
                $endMinutes = $this->timeToMinutes($endTime);
                
                Log::debug('Processing time range', [
                    'time_range' => $timeRange,
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes
                ]);
                
                // If class time falls within the available time range, high similarity
                if ($classMinutes >= $startMinutes && $classMinutes <= $endMinutes) {
                    $similarity = 1.0; // Perfect match
                    Log::debug('Perfect time match found', [
                        'similarity' => $similarity
                    ]);
                } else {
                    // Calculate distance-based similarity
                    $distance = min(
                        abs($classMinutes - $startMinutes),
                        abs($classMinutes - $endMinutes)
                    );
                    
                    // Use stricter threshold - only allow assignments within 2 hours (120 minutes)
                    $maxDistance = 120; // Reduced from 240 to 120 minutes
                    if ($distance > $maxDistance) {
                        $similarity = 0; // Too far apart
                        Log::debug('Time too far apart, similarity set to 0', [
                            'distance' => $distance,
                            'max_allowed' => $maxDistance
                        ]);
                    } else {
                        $similarity = max(0, 1 - ($distance / $maxDistance));
                        Log::debug('Distance-based similarity calculated', [
                            'similarity' => $similarity
                        ]);
                    }
                }
                
                // Apply preference bonus if available
                $similarity = $this->applyPreferenceBonus($similarity, $tutor, $classDayName, $classTime);
                
                $maxSimilarity = max($maxSimilarity, $similarity);
            }
        }
        
        Log::debug('Final new system similarity', [
            'tutor' => $tutor->tusername,
            'max_similarity' => $maxSimilarity
        ]);
        
        return $maxSimilarity;
    }

    /**
     * Calculate similarity using account-specific availability system
     */
    private function calculateAccountSystemSimilarity($classTime, $dayTimes, $tutorAccount, $classDayName)
    {
        $classMinutes = $classTime->hour * 60 + $classTime->minute;
        $maxSimilarity = 0;
        
        Log::debug('Calculating account system similarity', [
            'tutor_account' => $tutorAccount->tutor->tusername ?? 'unknown',
            'class_time' => $classTime->format('H:i'),
            'class_minutes' => $classMinutes,
            'day_times' => $dayTimes
        ]);
        
        foreach ($dayTimes as $timeRange) {
            if (strpos($timeRange, '-') !== false) {
                [$startTime, $endTime] = explode('-', $timeRange);
                
                $startMinutes = $this->timeToMinutes($startTime);
                $endMinutes = $this->timeToMinutes($endTime);
                
                Log::debug('Processing time range', [
                    'time_range' => $timeRange,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes
                ]);
                
                if ($classMinutes >= $startMinutes && $classMinutes <= $endMinutes) {
                    $similarity = 1.0; // Perfect match
                    Log::debug('Perfect time match found', [
                        'similarity' => $similarity,
                        'class_within_range' => true
                    ]);
                } else {
                    $distance = min(
                        abs($classMinutes - $startMinutes),
                        abs($classMinutes - $endMinutes)
                    );
                    
                    Log::debug('Time distance calculated', [
                        'distance_minutes' => $distance,
                        'distance_hours' => round($distance / 60, 2)
                    ]);
                    
                    $maxDistance = 120; 
                    if ($distance > $maxDistance) {
                        $similarity = 0; 
                        Log::debug('Time too far apart, similarity set to 0', [
                            'distance' => $distance,
                            'max_allowed' => $maxDistance
                        ]);
                    } else {
                        $similarity = max(0, 1 - ($distance / $maxDistance));
                        Log::debug('Distance-based similarity calculated', [
                            'similarity' => $similarity,
                            'distance' => $distance,
                            'max_distance' => $maxDistance
                        ]);
                    }
                }
                
                // Apply preference bonus if available
                $similarity = $this->applyAccountPreferenceBonus($similarity, $tutorAccount, $classDayName, $classTime);
                
                $maxSimilarity = max($maxSimilarity, $similarity);
                
                Log::debug('Time slot similarity final', [
                    'time_range' => $timeRange,
                    'final_similarity' => $similarity,
                    'max_similarity_so_far' => $maxSimilarity
                ]);
            }
        }
        
        Log::debug('Final account system similarity', [
            'tutor' => $tutorAccount->tutor->tusername ?? 'unknown',
            'max_similarity' => $maxSimilarity
        ]);
        
        return $maxSimilarity;
    }

    /**
     * Calculate similarity using old availability system (fallback)
     */
    private function calculateOldSystemSimilarity($classTime, $dayAvailabilities)
    {
        $maxSimilarity = 0;
        
        foreach ($dayAvailabilities as $availability) {
            $timeSlot = $availability->timeSlot;
            $similarity = $this->calculateTimeOverlapSimilarity($classTime, $timeSlot);
            
            if ($similarity > $maxSimilarity) {
                $maxSimilarity = $similarity;
            }
        }
        
        return $maxSimilarity;
    }

    /**
     * Convert time string to minutes since midnight
     */
    private function timeToMinutes($timeString)
    {
        $timeString = trim($timeString);
        
        // Handle different time formats
        if (preg_match('/(\d{1,2}):(\d{2})\s*(am|pm)?/i', $timeString, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            $period = isset($matches[3]) ? strtolower($matches[3]) : '';
            
            // Convert to 24-hour format if AM/PM is specified
            if ($period === 'pm' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'am' && $hour === 12) {
                $hour = 0;
            }
            
            return $hour * 60 + $minute;
        } else {
            // Fallback to Carbon parsing for standard formats
            try {
                $time = Carbon::createFromFormat('H:i', $timeString);
                return $time->hour * 60 + $time->minute;
            } catch (\Exception $e) {
                Log::warning('Could not parse time string', ['time' => $timeString]);
                return 0;
            }
        }
    }

    /**
     * Apply preference bonus based on tutor's preferred time range
     */
    private function applyPreferenceBonus($baseSimilarity, $tutor, $classDayName, $classTime)
    {
        if (!$tutor->preferred_time_range || $tutor->preferred_time_range === 'flexible') {
            return $baseSimilarity;
        }
        
        $classHour = $classTime->hour;
        $preferenceMatch = false;
        
        switch ($tutor->preferred_time_range) {
            case 'morning':
                $preferenceMatch = $classHour >= 6 && $classHour < 12;
                break;
            case 'afternoon':
                $preferenceMatch = $classHour >= 12 && $classHour < 17;
                break;
            case 'evening':
                $preferenceMatch = $classHour >= 17 && $classHour < 23;
                break;
        }
        
        // Apply 20% bonus for preference match
        return $preferenceMatch ? $baseSimilarity * 1.2 : $baseSimilarity;
    }

    /**
     * Apply preference bonus based on tutor account's preferred time range
     */
    private function applyAccountPreferenceBonus($baseSimilarity, $tutorAccount, $classDayName, $classTime)
    {
        if (!$tutorAccount->preferred_time_range || $tutorAccount->preferred_time_range === 'flexible') {
            return $baseSimilarity;
        }
        
        $classHour = $classTime->hour;
        $preferenceMatch = false;
        
        switch ($tutorAccount->preferred_time_range) {
            case 'morning':
                $preferenceMatch = $classHour >= 6 && $classHour < 12;
                break;
            case 'afternoon':
                $preferenceMatch = $classHour >= 12 && $classHour < 17;
                break;
            case 'evening':
                $preferenceMatch = $classHour >= 17 && $classHour < 23;
                break;
        }
        
        // Apply 20% bonus for preference match
        return $preferenceMatch ? $baseSimilarity * 1.2 : $baseSimilarity;
    }

    /**
     * Auto-assign tutors for a specific date
     */
    public function autoAssignTutorsForSpecificSchedule($date, $day = null)
    {
        return $this->autoAssignTutors($date, $day);
    }

    /**
     * Auto-assign tutors for a specific class
     */
    public function autoAssignTutorsForSpecificClass($class)
    {
        $needMore = $class->number_required - $class->tutorAssignments()->count();
        
        if ($needMore <= 0) {
            return ['assigned' => 0, 'assignments' => []];
        }
        
        $bestTutors = $this->findBestTutorsForClass($class, $needMore);
        $assignedCount = 0;
        $results = [];
        
        foreach ($bestTutors as $tutorMatch) {
            // Check if tutor is not already assigned to this class
            $existingAssignment = TutorAssignment::where('daily_data_id', $class->id)
                ->where('tutor_id', $tutorMatch['tutor']->tutorID)
                ->first();
                
            if (!$existingAssignment) {
                TutorAssignment::create([
                    'daily_data_id' => $class->id,
                    'tutor_id' => $tutorMatch['tutor']->tutorID,
                    'assigned_at' => now(),
                ]);
                
                $assignedCount++;
                $results[] = [
                    'class_id' => $class->id,
                    'tutor_id' => $tutorMatch['tutor']->tutorID,
                    'tutor_name' => $tutorMatch['tutor']->tusername,
                    'similarity_score' => $tutorMatch['similarity'],
                    'school' => $class->school,
                    'class_name' => $class->class
                ];
            }
        }
        
        return [
            'assigned' => $assignedCount,
            'assignments' => $results
        ];
    }

    /**
     * Create history records for assignment actions
     */
    private function createAssignmentHistoryRecords($assignments)
    {
        // Get current supervisor - prioritize authenticated user over session
        $authCheck = Auth::guard('supervisor')->check();
        $authUser = Auth::guard('supervisor')->user();
        $sessionSupervisorId = session('supervisor_id');
        
        Log::debug('Auto-assignment service supervisor detection', [
            'session_supervisor_id' => $sessionSupervisorId,
            'auth_check' => $authCheck,
            'auth_user' => $authUser ? $authUser->toArray() : null,
            'all_session_data' => session()->all()
        ]);
        
        // Prioritize authenticated user over session data
        $supervisorId = null;
        if ($authCheck && $authUser) {
            $supervisorId = $authUser->supID;
            Log::debug('Using authenticated supervisor ID', ['supervisor_id' => $supervisorId]);
        } elseif ($sessionSupervisorId) {
            $supervisorId = $sessionSupervisorId;
            Log::debug('Using session supervisor ID', ['supervisor_id' => $supervisorId]);
        }
        
        if (!$supervisorId) {
            Log::warning('No supervisor ID found for assignment history creation');
            return;
        }
        
        // Group assignments by class
        $classAssignments = [];
        foreach ($assignments as $assignment) {
            $classId = $assignment['class_id'];
            if (!isset($classAssignments[$classId])) {
                $classAssignments[$classId] = [];
            }
            $classAssignments[$classId][] = $assignment;
        }
        
        // Create history record for each class
        foreach ($classAssignments as $classId => $classAssignmentList) {
            $class = DailyData::find($classId);
            if (!$class) continue;
            
            // Set schedule ownership for all classes on this date if not already assigned
            $scheduleDate = $class->date;
            if (!$class->isAssigned()) {
                // Assign all classes on this date to the current supervisor
                DailyData::where('date', $scheduleDate)
                    ->whereNull('assigned_supervisor')
                    ->update([
                        'assigned_supervisor' => $supervisorId,
                        'assigned_at' => now()
                    ]);
                
                Log::info("Schedule assigned to supervisor via auto-assignment", [
                    'class_id' => $class->id,
                    'supervisor_id' => $supervisorId,
                    'class_name' => $class->class,
                    'schedule_date' => $scheduleDate
                ]);
            } elseif (!$class->isAssignedTo($supervisorId)) {
                Log::warning("Skipping auto-assignment for schedule owned by another supervisor", [
                    'class_id' => $class->id,
                    'current_supervisor' => $supervisorId,
                    'assigned_supervisor' => $class->assigned_supervisor,
                    'class_name' => $class->class
                ]);
                continue; // Skip this class
            }
            
            $tutorNames = array_column($classAssignmentList, 'tutor_name');
            
            $class->createHistoryRecord(
                'assigned',
                $supervisorId,
                'Auto-assigned tutors to class',
                [
                    'previous_assigned_count' => $class->tutorAssignments()->count() - count($tutorNames)
                ],
                [
                    'assigned_tutors' => $tutorNames,
                    'final_assigned_count' => $class->tutorAssignments()->count(),
                    'assignment_type' => 'auto'
                ]
            );
        }
    }

    /**
     * Remove tutor assignment
     */
    public function removeTutorAssignment($assignmentId)
    {
        $assignment = TutorAssignment::find($assignmentId);
        
        if ($assignment) {
            $assignment->delete();
            return true;
        }
        
        return false;
    }

    /**
     * Validate and sanitize day name to prevent SQL injection
     */
    private function validateDayName($day)
    {
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $day = strtolower(trim($day));
        
        // Handle abbreviated day names
        if (strlen($day) <= 4) {
            $dayMap = [
                'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                'thur' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'
            ];
            $normalizedDay = $dayMap[$day] ?? ucfirst($day);
        } else {
            // Handle full day names (lowercase)
            $fullDayMap = [
                'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'
            ];
            $normalizedDay = $fullDayMap[$day] ?? ucfirst($day);
        }
        
        // Only return if it's a valid day name
        if (in_array($normalizedDay, $validDays)) {
            return $normalizedDay;
        }
        
        // Return null for invalid day names
        return null;
    }
}