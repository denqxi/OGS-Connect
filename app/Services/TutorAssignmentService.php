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
        Log::info('🚀 AUTO-ASSIGN STARTED', [
            'date' => $date, 
            'day' => $day, 
            'account' => $accountName,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
        
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

        Log::info('📋 CLASSES FOUND', [
            'total_classes' => $classes->count(),
            'classes' => $classes->map(function($class) {
                return [
                    'id' => $class->id,
                    'class' => $class->class,
                    'date' => $class->date,
                    'time' => $class->time_jst,
                    'required' => $class->number_required,
                    'assigned' => $class->tutorAssignments()->count()
                ];
            })->toArray()
        ]);

        $assignedCount = 0;
        $results = [];

        foreach ($classes as $class) {
            $currentAssigned = $class->tutorAssignments()->count();
            $needMore = $class->number_required - $currentAssigned;
            
            Log::info('🎯 PROCESSING CLASS', [
                'class_id' => $class->id,
                'school' => $class->school,
                'class_name' => $class->class,
                'date' => $class->date,
                'time' => $class->time_jst,
                'day' => Carbon::parse($class->date)->format('l'),
                'current_assigned' => $currentAssigned,
                'required' => $class->number_required,
                'need_more' => $needMore
            ]);
            
            if ($needMore > 0) {
                $bestTutors = $this->findBestTutorsForClass($class, $needMore, $accountName);
                
                if (count($bestTutors) > 0) {
                    Log::info('👥 TUTORS FOUND', [
                        'count' => count($bestTutors),
                        'tutors' => array_map(function($match) {
                            return [
                                'tutor_id' => $match['tutor']->tutorID,
                                'username' => $match['tutor']->tusername,
                                'similarity' => $match['similarity']
                            ];
                        }, $bestTutors)
                    ]);
                } else {
                    Log::info('❌ NO TUTORS FOUND', [
                        'class_id' => $class->id,
                        'class_name' => $class->class,
                        'date' => $class->date,
                        'time' => $class->time_jst,
                        'day' => Carbon::parse($class->date)->format('l'),
                        'reason' => 'No tutors available at this time or day'
                    ]);
                }
                
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
                            'reliability_score' => $tutorMatch['reliability_score'] ?? 0,
                            'combined_score' => $tutorMatch['combined_score'] ?? $tutorMatch['similarity'],
                            'school' => $class->school,
                            'class_name' => $class->class,
                            'date' => $class->date,
                            'time' => $class->time_jst
                        ];
                        
                        Log::info('✅ TUTOR ASSIGNED', [
                            'tutor_id' => $tutorMatch['tutor']->tutorID,
                            'tutor_username' => $tutorMatch['tutor']->tusername,
                            'class_id' => $class->id,
                            'class_name' => $class->class,
                            'school' => $class->school,
                            'date' => $class->date,
                            'time' => $class->time_jst,
                            'similarity_score' => $tutorMatch['similarity'],
                            'reliability_score' => $tutorMatch['reliability_score'] ?? 0,
                            'combined_score' => $tutorMatch['combined_score'] ?? $tutorMatch['similarity'],
                            'assignment_reason' => 'Auto-assigned based on time availability and reliability history',
                            'assigned_at' => now()->format('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

        // Create history records for classes that had tutors assigned
        if ($assignedCount > 0) {
            $this->createAssignmentHistoryRecords($results);
        }

        Log::info('🏁 AUTO-ASSIGN COMPLETED', [
            'total_assigned' => $assignedCount,
            'total_classes_processed' => $classes->count(),
            'assignments' => $results,
            'completed_at' => now()->format('Y-m-d H:i:s')
        ]);

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
        
        // Convert JST to PHT for comparison with tutor availability (PHT is 1 hour behind JST)
        $classTimePht = $classTime->copy()->subHour();
        
        Log::info('🔍 SEARCHING FOR TUTORS', [
            'class_day' => $classDayName,
            'class_time_jst' => $classTime->format('H:i:s'),
            'class_time_pht' => $classTimePht->format('H:i:s'),
            'class_time_minutes_jst' => $classTime->hour * 60 + $classTime->minute,
            'class_time_minutes_pht' => $classTimePht->hour * 60 + $classTimePht->minute,
            'account' => $accountName,
            'total_eligible_tutors' => $availableTutors->count(),
            'limit_needed' => $limit
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
                
                // Calculate best similarity for this day using PHT time
                $maxSimilarity = $this->calculateAccountSystemSimilarity($classTimePht, $dayTimes, $tutorAccount, $classDayName);
                
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
                    
                    $maxSimilarity = $this->calculateNewSystemSimilarity($classTimePht, $dayTimes, $tutor, $classDayName);
                } else {
                    // No availability data found - skip this tutor
                    continue;
                }
            }

            // Only consider tutors with good similarity (> 0.8 for strict time matching)
            // This ensures tutors are only assigned if they're actually available at the class time
            if ($maxSimilarity > 0.8) {
                // Calculate reliability score for this tutor
                $reliabilityScore = $this->calculateTutorReliabilityScore($tutor);
                
                // Calculate combined score (time availability + reliability)
                $combinedScore = $this->calculateCombinedTutorScore($maxSimilarity, $reliabilityScore);
                
                $tutorSimilarities[] = [
                    'tutor' => $tutor,
                    'similarity' => $maxSimilarity,
                    'reliability_score' => $reliabilityScore,
                    'combined_score' => $combinedScore
                ];
                
                Log::info('✅ TUTOR AVAILABLE', [
                    'tutor_id' => $tutor->tutorID,
                    'tutor_username' => $tutor->tusername,
                    'similarity_score' => $maxSimilarity,
                    'reliability_score' => $reliabilityScore,
                    'combined_score' => $combinedScore,
                    'class_time_jst' => $classTime->format('H:i:s'),
                    'class_time_pht' => $classTimePht->format('H:i:s'),
                    'class_day' => $classDayName
                ]);
            } else {
                Log::info('❌ TUTOR NOT AVAILABLE', [
                    'tutor_id' => $tutor->tutorID,
                    'tutor_username' => $tutor->tusername,
                    'similarity_score' => $maxSimilarity,
                    'threshold' => 0.8,
                    'class_time_jst' => $classTime->format('H:i:s'),
                    'class_time_pht' => $classTimePht->format('H:i:s'),
                    'class_day' => $classDayName,
                    'reason' => 'Not available at this time or day'
                ]);
            }
        }

        // Sort by combined score (highest first) which considers both time availability and reliability
        usort($tutorSimilarities, function($a, $b) {
            // Primary sort: Combined score (descending)
            $combinedComparison = $b['combined_score'] <=> $a['combined_score'];
            if ($combinedComparison !== 0) {
                return $combinedComparison;
            }
            
            // Secondary sort: Reliability score (descending) - prefer more reliable tutors
            $reliabilityComparison = $b['reliability_score'] <=> $a['reliability_score'];
            if ($reliabilityComparison !== 0) {
                return $reliabilityComparison;
            }
            
            // Tertiary sort: Time similarity (descending) - prefer better time matches
            return $b['similarity'] <=> $a['similarity'];
        });

        $topMatches = array_slice($tutorSimilarities, 0, $limit);
        
        Log::info('Top tutor matches found (prioritized by reliability)', [
            'count' => count($topMatches),
            'matches' => array_map(function($match) {
                return [
                    'tutor' => $match['tutor']->tusername,
                    'similarity' => round($match['similarity'], 3),
                    'reliability' => round($match['reliability_score'], 3),
                    'combined_score' => round($match['combined_score'], 3)
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
                    $similarity = 1.0; // Perfect match - tutor is available at this exact time
                    Log::debug('Perfect time match found', [
                        'similarity' => $similarity
                    ]);
                } else {
                    // Tutor is not available at this time - set similarity to 0
                    $similarity = 0;
                    Log::debug('Tutor not available at class time', [
                        'class_time_minutes' => $classMinutes,
                        'available_start' => $startMinutes,
                        'available_end' => $endMinutes,
                        'similarity' => $similarity
                    ]);
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
                    $similarity = 1.0; // Perfect match - tutor is available at this exact time
                    Log::debug('Perfect time match found', [
                        'similarity' => $similarity,
                        'class_within_range' => true
                    ]);
                } else {
                    // Tutor is not available at this time - set similarity to 0
                    $similarity = 0;
                    Log::debug('Tutor not available at class time', [
                        'class_time_minutes' => $classMinutes,
                        'available_start' => $startMinutes,
                        'available_end' => $endMinutes,
                        'similarity' => $similarity
                    ]);
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

    /**
     * Calculate tutor reliability score based on attendance vs cancellation history
     * Higher score = more reliable tutor (fewer cancellations)
     * Score range: 0.0 to 1.0
     */
    private function calculateTutorReliabilityScore($tutor)
    {
        // Look at the last 3 months of assignment history
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        
        // Count total assignments for this tutor in the last 3 months
        $totalAssignments = TutorAssignment::where('tutor_id', $tutor->tutorID)
            ->whereHas('dailyData', function($query) use ($threeMonthsAgo) {
                $query->where('date', '>=', $threeMonthsAgo->format('Y-m-d'));
            })
            ->count();
        
        // Count cancelled assignments in the last 3 months
        $cancelledAssignments = TutorAssignment::where('tutor_id', $tutor->tutorID)
            ->where('status', 'cancelled')
            ->whereHas('dailyData', function($query) use ($threeMonthsAgo) {
                $query->where('date', '>=', $threeMonthsAgo->format('Y-m-d'));
            })
            ->count();
        
        // If no assignment history, give benefit of the doubt with a neutral score
        if ($totalAssignments === 0) {
            return 0.7; // Neutral score for new tutors
        }
        
        // Calculate reliability score
        $cancellationRate = $cancelledAssignments / $totalAssignments;
        $reliabilityScore = 1.0 - $cancellationRate;
        
        // Apply some scaling to ensure tutors with good track records get priority
        // Tutors with 90%+ attendance get boosted scores
        if ($reliabilityScore >= 0.9) {
            $reliabilityScore = min(1.0, $reliabilityScore * 1.1); // Boost reliable tutors
        }
        
        // Tutors with high cancellation rates (>30%) get penalized more
        if ($cancellationRate > 0.3) {
            $reliabilityScore = max(0.1, $reliabilityScore * 0.7); // Penalize unreliable tutors
        }
        
        Log::debug('Tutor reliability calculated', [
            'tutor_id' => $tutor->tutorID,
            'tutor_username' => $tutor->tusername,
            'total_assignments' => $totalAssignments,
            'cancelled_assignments' => $cancelledAssignments,
            'cancellation_rate' => round($cancellationRate, 3),
            'reliability_score' => round($reliabilityScore, 3)
        ]);
        
        return max(0.0, min(1.0, $reliabilityScore)); // Ensure score is between 0 and 1
    }

    /**
     * Calculate combined score for tutor prioritization
     * Combines time availability similarity with reliability score
     */
    private function calculateCombinedTutorScore($similarity, $reliabilityScore)
    {
        // Weight the scores: 60% similarity (time availability), 40% reliability
        $timeWeight = 0.6;
        $reliabilityWeight = 0.4;
        
        $combinedScore = ($similarity * $timeWeight) + ($reliabilityScore * $reliabilityWeight);
        
        return $combinedScore;
    }
}