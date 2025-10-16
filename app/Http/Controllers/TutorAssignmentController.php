<?php

namespace App\Http\Controllers;

use App\Models\DailyData;
use App\Models\Tutor;
use App\Models\TutorAssignment;
use App\Models\ScheduleHistory;
use App\Models\AuditLog;
use App\Services\TutorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TutorAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(TutorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Auto-assign tutors to all available classes
     */
    public function autoAssign(Request $request)
    {
        try {
            $date = $request->input('date');
            $day = $request->input('day');
            
            $result = $this->assignmentService->autoAssignTutors($date, $day);
            
            // Log auto-assignment activity
            $currentUser = $this->getCurrentAuthenticatedUser();
            $dateInfo = $date ? "for date {$date}" : ($day ? "for {$day}" : "for available classes");
            
            AuditLog::logEvent(
                'auto_assignment_completed',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Auto-Assignment Completed',
                "Auto-assignment {$dateInfo} completed by {$currentUser['name']}. {$result['assigned']} tutors automatically assigned to classes.",
                [
                    'date' => $date,
                    'day' => $day,
                    'assigned_count' => $result['assigned'],
                    'assignment_details' => $result
                ],
                'medium',
                true
            );
            
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
    public function autoAssignForDate(Request $request, $date)
    {
        try {
            $result = $this->assignmentService->autoAssignTutorsForSpecificSchedule($date, null);
            
            // Log auto-assignment for specific date
            $currentUser = $this->getCurrentAuthenticatedUser();
            
            AuditLog::logEvent(
                'auto_assignment_specific_date',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Auto-Assignment for Specific Date',
                "Auto-assignment for specific date {$date} completed by {$currentUser['name']}. {$result['assigned']} tutors automatically assigned.",
                [
                    'date' => $date,
                    'assigned_count' => $result['assigned'],
                    'assignment_details' => $result
                ],
                'medium',
                true
            );
            
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
    public function autoAssignForSpecific(Request $request, $date, $day)
    {
        try {
            $result = $this->assignmentService->autoAssignTutorsForSpecificSchedule($date, $day);
            
            // Log auto-assignment for specific date and day
            $currentUser = $this->getCurrentAuthenticatedUser();
            
            AuditLog::logEvent(
                'auto_assignment_specific_date_day',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Auto-Assignment for Specific Date and Day',
                "Auto-assignment for {$date} ({$day}) completed by {$currentUser['name']}. {$result['assigned']} tutors automatically assigned.",
                [
                    'date' => $date,
                    'day' => $day,
                    'assigned_count' => $result['assigned'],
                    'assignment_details' => $result
                ],
                'medium',
                true
            );
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed for {$date} ({$day}). {$result['assigned']} tutors assigned.",
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
    public function autoAssignForClass(Request $request, $classId)
    {
        try {
            $class = DailyData::findOrFail($classId);
            
            $result = $this->assignmentService->autoAssignTutorsForClass($class);
            
            // Log auto-assignment for specific class
            $currentUser = $this->getCurrentAuthenticatedUser();
            
            AuditLog::logEvent(
                'auto_assignment_single_class',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Auto-Assignment for Single Class',
                "Auto-assignment for class {$class->class} at {$class->school} on {$class->date} completed by {$currentUser['name']}. {$result['assigned']} tutors automatically assigned.",
                [
                    'class_id' => $classId,
                    'class_name' => $class->class,
                    'school' => $class->school,
                    'date' => $class->date,
                    'assigned_count' => $result['assigned'],
                    'assignment_details' => $result
                ],
                'low',
                true
            );
            
            return response()->json([
                'success' => true,
                'message' => "Auto-assignment completed for class {$class->class}. {$result['assigned']} tutors assigned.",
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
    public function removeAssignment(Request $request, $assignmentId)
    {
        try {
            // Validate the cancellation reason
            $request->validate([
                'cancellation_reason' => 'required|string|max:1000'
            ]);

            $assignment = TutorAssignment::findOrFail($assignmentId);
            
            // Check if user has permission to remove this assignment
            $this->checkAssignmentPermission($assignment);
            
            DB::beginTransaction();
            
            // Get tutor and class information for logging
            $tutorName = $assignment->tutor ? $assignment->tutor->tusername : 'Unknown';
            $className = $assignment->dailyData ? $assignment->dailyData->class : 'Unknown';
            $schoolName = $assignment->dailyData ? $assignment->dailyData->school : 'Unknown';
            $classDate = $assignment->dailyData ? $assignment->dailyData->date : 'Unknown';
            $supervisorId = Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->supID : null;
            
            // Mark assignment as cancelled instead of deleting it
            $assignment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
                'cancelled_by' => $supervisorId
            ]);
            
            // Log to audit system
            $currentUser = $this->getCurrentAuthenticatedUser();
            AuditLog::logEvent(
                'tutor_cancellation',
                $currentUser['type'],
                $currentUser['id'],
                $currentUser['email'],
                $currentUser['name'],
                'Tutor Assignment Cancelled',
                "Tutor {$tutorName} cancelled from {$className} at {$schoolName} on {$classDate} by {$currentUser['name']}. Reason: {$request->cancellation_reason}",
                ['tutor_name' => $tutorName, 'class' => $className, 'school' => $schoolName, 'date' => $classDate, 'reason' => $request->cancellation_reason],
                'medium',
                true
            );
            
            // Log the removal with reason
            Log::info('Tutor assignment cancelled', [
                'assignment_id' => $assignmentId,
                'tutor_name' => $tutorName,
                'class_name' => $className,
                'school_name' => $schoolName,
                'class_date' => $classDate,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => $supervisorId,
                'cancelled_at' => now()
            ]);
            
            // Create a history record if the class supports it
            if ($assignment->dailyData && method_exists($assignment->dailyData, 'createHistoryRecord')) {
                $assignment->dailyData->createHistoryRecord(
                    'tutor_cancelled',
                    $supervisorId,
                    "Tutor {$tutorName} cancelled from assignment: {$request->cancellation_reason}",
                    [
                        'previous_tutor' => $tutorName,
                        'assignment_id' => $assignmentId
                    ],
                    [
                        'cancellation_reason' => $request->cancellation_reason,
                        'cancelled_at' => now(),
                        'cancelled_by' => $supervisorId
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Tutor {$tutorName} has been cancelled from {$className}. Reason: {$request->cancellation_reason}"
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cancelling tutor assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel tutor assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save tutor assignments for a class
     */
    public function saveAssignments(Request $request)
    {
        try {
            $classId = $request->input('class_id');
            $tutors = $request->input('tutors', []); // Main tutors (usernames)
            $backupTutor = $request->input('backup_tutor'); // Backup tutor object
            $tutorsToRemove = $request->input('tutors_to_remove', []); // Tutors to remove with reasons
            
            $class = DailyData::findOrFail($classId);
            
            // Check permission
            $this->checkClassPermission($class);
            
            DB::beginTransaction();
            
            // Handle tutor removals first (with cancellation reasons)
            $removedTutors = [];
            foreach ($tutorsToRemove as $removalData) {
                if (empty($removalData['tutor_username']) || empty($removalData['cancellation_reason'])) {
                    continue;
                }
                
                $tutorUsername = $removalData['tutor_username'];
                $cancellationReason = $removalData['cancellation_reason'];
                
                // Find the tutor and assignment
                $tutor = Tutor::where('tusername', $tutorUsername)->first();
                if (!$tutor) continue;
                
                $assignment = TutorAssignment::where('daily_data_id', $classId)
                    ->where('tutor_id', $tutor->tutorID)
                    ->first();
                
                if ($assignment) {
                    // Update assignment status to cancelled with reason
                    $assignment->update([
                        'status' => 'cancelled',
                        'cancellation_reason' => $cancellationReason,
                        'cancelled_at' => now(),
                        'cancelled_by' => Auth::user()->name ?? 'System'
                    ]);
                    
                    $removedTutors[] = ['username' => $tutorUsername, 'reason' => $cancellationReason];
                    
                    // Log the cancellation in schedule history
                    ScheduleHistory::create([
                        'class_id' => $classId,
                        'class_name' => $class->class,
                        'school' => $class->school,
                        'class_date' => $class->date,
                        'class_time' => $class->time_jst,
                        'status' => $class->status ?? 'draft',
                        'action' => 'tutor_cancelled',
                        'performed_by' => Auth::user()->name ?? 'System',
                        'reason' => $cancellationReason,
                        'old_data' => [
                            'tutor_username' => $tutorUsername,
                            'tutor_name' => $tutor->first_name . ' ' . $tutor->last_name,
                            'role' => $assignment->is_backup ? 'backup' : 'main',
                            'assignment_status' => 'assigned'
                        ],
                        'new_data' => [
                            'tutor_username' => $tutorUsername,
                            'tutor_name' => $tutor->first_name . ' ' . $tutor->last_name,
                            'role' => $assignment->is_backup ? 'backup' : 'main',
                            'assignment_status' => 'cancelled',
                            'cancellation_reason' => $cancellationReason,
                            'cancelled_by' => Auth::user()->name ?? 'System'
                        ]
                    ]);
                }
            }
            
            // Log tutor removals to audit system
            if (!empty($removedTutors)) {
                $currentUser = $this->getCurrentAuthenticatedUser();
                foreach ($removedTutors as $removed) {
                    AuditLog::logEvent(
                        'tutor_removal',
                        $currentUser['type'],
                        $currentUser['id'],
                        $currentUser['email'],
                        $currentUser['name'],
                        'Tutor Removed from Class',
                        "Tutor {$removed['username']} removed from {$class->class} at {$class->school} on {$class->date} by {$currentUser['name']}. Reason: {$removed['reason']}",
                        ['tutor' => $removed['username'], 'class' => $class->class, 'school' => $class->school, 'date' => $class->date, 'reason' => $removed['reason']],
                        'medium',
                        true
                    );
                }
            }
            
            // Remove existing NON-CANCELLED assignments to prepare for new ones
            TutorAssignment::where('daily_data_id', $classId)
                ->where('status', '!=', 'cancelled')
                ->delete();
            
            // Add main tutors
            $assignedTutors = [];
            foreach ($tutors as $tutorUsername) {
                if (empty($tutorUsername)) continue;
                
                $tutor = Tutor::where('tusername', $tutorUsername)->first();
                if ($tutor) {
                    TutorAssignment::create([
                        'daily_data_id' => $classId,
                        'tutor_id' => $tutor->tutorID,
                        'is_backup' => false,
                        'assigned_at' => now(),
                        'status' => 'assigned'
                    ]);
                    $assignedTutors[] = $tutor->tusername;
                }
            }
            
            // Add backup tutor if provided
            $backupTutorAssigned = null;
            if ($backupTutor && !empty($backupTutor['username'])) {
                $backupTutorModel = Tutor::where('tusername', $backupTutor['username'])->first();
                if ($backupTutorModel) {
                    TutorAssignment::create([
                        'daily_data_id' => $classId,
                        'tutor_id' => $backupTutorModel->tutorID,
                        'is_backup' => true,
                        'assigned_at' => now(),
                        'status' => 'assigned'
                    ]);
                    $backupTutorAssigned = $backupTutorModel->tusername;
                }
            }
            
            // Log tutor assignments to audit system
            if (!empty($assignedTutors) || $backupTutorAssigned) {
                $currentUser = $this->getCurrentAuthenticatedUser();
                $tutorList = implode(', ', $assignedTutors);
                if ($backupTutorAssigned) {
                    $tutorList .= ($tutorList ? ' (backup: ' . $backupTutorAssigned . ')' : 'backup: ' . $backupTutorAssigned);
                }
                
                AuditLog::logEvent(
                    'tutor_assignment',
                    $currentUser['type'],
                    $currentUser['id'],
                    $currentUser['email'],
                    $currentUser['name'],
                    'Tutors Assigned to Class',
                    "Tutors assigned to {$class->class} at {$class->school} on {$class->date} by {$currentUser['name']}: {$tutorList}",
                    ['class' => $class->class, 'school' => $class->school, 'date' => $class->date, 'tutors' => $tutorList],
                    'low',
                    true
                );
            }
            
            // Assign supervisor to the class if not already assigned
            $this->assignSupervisorToClass($class);
            
            DB::commit();
            
            $removedCount = count($tutorsToRemove);
            $message = 'Tutor assignments saved successfully';
            if ($removedCount > 0) {
                $message .= ". {$removedCount} tutor(s) removed with cancellation reasons.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving tutor assignments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tutor assignments: ' . $e->getMessage()
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
            
            // Get active (non-cancelled) assignments
            $mainTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where(function($query) {
                    $query->where('is_backup', false)
                          ->orWhereNull('is_backup');
                })
                ->where('status', '!=', 'cancelled')
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_promoted' => $assignment->was_promoted_from_backup,
                        'replaced_tutor' => $assignment->replaced_tutor_name,
                        'status' => $assignment->status,
                        'assigned_at' => $assignment->assigned_at
                    ];
                });

            $backupTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('is_backup', true)
                ->where('status', '!=', 'cancelled')
                ->with('tutor')
                ->get()
                ->map(function($assignment) {
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_promoted' => $assignment->was_promoted_from_backup,
                        'replaced_tutor' => $assignment->replaced_tutor_name,
                        'status' => $assignment->status,
                        'assigned_at' => $assignment->assigned_at
                    ];
                });

            // Get cancelled tutors with their cancellation reasons
            $cancelledTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('status', 'cancelled')
                ->with(['tutor'])
                ->orderBy('cancelled_at', 'desc')
                ->get()
                ->map(function($assignment) {
                    $cancelledBy = null;
                    if ($assignment->cancelled_by) {
                        // Try to get supervisor info
                        $supervisor = \App\Models\Supervisor::where('supID', $assignment->cancelled_by)->first();
                        $cancelledBy = $supervisor ? $supervisor->full_name : $assignment->cancelled_by;
                    }
                    
                    return [
                        'username' => $assignment->tutor->tusername,
                        'full_name' => $assignment->tutor->full_name,
                        'was_backup' => $assignment->is_backup,
                        'cancellation_reason' => $assignment->cancellation_reason,
                        'cancelled_at' => $assignment->cancelled_at,
                        'cancelled_by' => $cancelledBy,
                        'assigned_at' => $assignment->assigned_at
                    ];
                });

            return response()->json([
                'success' => true,
                'main_tutors' => $mainTutors,
                'backup_tutors' => $backupTutors,
                'cancelled_tutors' => $cancelledTutors,
                'class_info' => [
                    'id' => $class->id,
                    'name' => $class->class,
                    'required' => $class->number_required
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting class tutors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get class tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has permission to modify assignment
     */
    private function checkAssignmentPermission($assignment)
    {
        // Add your permission logic here
        // For now, allowing all operations
        return true;
    }

    /**
     * Check if user has permission to modify class
     */
    private function checkClassPermission($class)
    {
        // Add your permission logic here
        // For now, allowing all operations
        return true;
    }

    /**
     * Assign supervisor to class if not already assigned
     */
    private function assignSupervisorToClass($class)
    {
        // Get current supervisor ID
        $currentSupervisorId = null;
        if (Auth::guard('supervisor')->check()) {
            $currentSupervisorId = Auth::guard('supervisor')->user()->supID;
        } elseif (session('supervisor_id')) {
            $currentSupervisorId = session('supervisor_id');
        }

        // Only assign if we have a supervisor ID and the class is not already assigned
        if ($currentSupervisorId && !$class->assigned_supervisor) {
            $class->update([
                'assigned_supervisor' => $currentSupervisorId,
                'assigned_at' => now()
            ]);

            Log::info("Supervisor assigned to class via manual tutor assignment", [
                'class_id' => $class->id,
                'supervisor_id' => $currentSupervisorId,
                'class_name' => $class->class,
                'schedule_date' => $class->date
            ]);
        }
    }

    /**
     * Remove a specific tutor assignment with cancellation reason
     */
    public function removeTutorAssignment(Request $request)
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:daily_data,id',
                'tutor_username' => 'required|string',
                'cancellation_reason' => 'required|string|max:1000',
            ]);

            $classId = $request->class_id;
            $tutorUsername = $request->tutor_username;
            $cancellationReason = $request->cancellation_reason;

            // First, find the tutor by username
            $tutor = Tutor::where('tusername', $tutorUsername)->first();
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not found with username: ' . $tutorUsername
                ], 404);
            }

            // Find the tutor assignment using tutor_id
            $assignment = TutorAssignment::where('daily_data_id', $classId)
                ->where('tutor_id', $tutor->tutorID)
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor assignment not found or already cancelled'
                ], 404);
            }

            // Update assignment with cancellation data
            $assignment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $cancellationReason,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::user()->name ?? 'System'
            ]);

            // Get the class details for logging
            $class = DailyData::findOrFail($classId);

            // Log the cancellation in schedule history
            ScheduleHistory::create([
                'class_id' => $classId,
                'class_name' => $class->class,
                'school' => $class->school,
                'class_date' => $class->date,
                'class_time' => $class->time_jst,
                'status' => $class->status ?? 'draft',
                'action' => 'tutor_cancelled',
                'performed_by' => Auth::user()->name ?? 'System',
                'reason' => $cancellationReason,
                'old_data' => [
                    'tutor_username' => $tutorUsername,
                    'tutor_name' => $tutor->first_name . ' ' . $tutor->last_name,
                    'role' => $assignment->is_backup ? 'backup' : 'main',
                    'assignment_status' => 'assigned'
                ],
                'new_data' => [
                    'tutor_username' => $tutorUsername,
                    'tutor_name' => $tutor->first_name . ' ' . $tutor->last_name,
                    'role' => $assignment->is_backup ? 'backup' : 'main',
                    'assignment_status' => 'cancelled',
                    'cancellation_reason' => $cancellationReason,
                    'cancelled_by' => Auth::user()->name ?? 'System'
                ]
            ]);

            // Get updated cancelled tutors list for this class
            $cancelledTutors = TutorAssignment::where('daily_data_id', $classId)
                ->where('status', 'cancelled')
                ->with('tutor')
                ->get()
                ->map(function ($assignment) {
                    return [
                        'full_name' => $assignment->tutor->first_name . ' ' . $assignment->tutor->last_name,
                        'username' => $assignment->tutor->tusername,
                        'role' => $assignment->is_backup ? 'backup' : 'main',
                        'cancellation_reason' => $assignment->cancellation_reason,
                        'cancelled_at' => $assignment->cancelled_at,
                        'cancelled_by' => $assignment->cancelled_by,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Tutor assignment cancelled successfully',
                'cancelled_tutors' => $cancelledTutors
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error removing tutor assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the tutor assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all cancelled tutors for API
     */
    public function getCancelledTutors(Request $request)
    {
        try {
            $query = TutorAssignment::where('status', 'cancelled')
                ->with(['tutor', 'dailyData'])
                ->orderBy('cancelled_at', 'desc');

            // Filter by date range if provided
            if ($request->filled('start_date')) {
                $query->where('cancelled_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('cancelled_at', '<=', $request->end_date . ' 23:59:59');
            }

            $cancelledAssignments = $query->paginate(20);

            $results = $cancelledAssignments->map(function($assignment) {
                $cancelledBy = null;
                if ($assignment->cancelled_by) {
                    $supervisor = \App\Models\Supervisor::where('supID', $assignment->cancelled_by)->first();
                    $cancelledBy = $supervisor ? $supervisor->full_name : $assignment->cancelled_by;
                }

                return [
                    'id' => $assignment->id,
                    'tutor_name' => $assignment->tutor->full_name,
                    'tutor_username' => $assignment->tutor->tusername,
                    'class_name' => $assignment->dailyData->class,
                    'school' => $assignment->dailyData->school,
                    'date' => $assignment->dailyData->date,
                    'time' => $assignment->dailyData->time_jst,
                    'role' => $assignment->is_backup ? 'Backup' : 'Main',
                    'cancellation_reason' => $assignment->cancellation_reason,
                    'cancelled_at' => $assignment->cancelled_at,
                    'cancelled_by' => $cancelledBy,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $results,
                'pagination' => [
                    'current_page' => $cancelledAssignments->currentPage(),
                    'last_page' => $cancelledAssignments->lastPage(),
                    'per_page' => $cancelledAssignments->perPage(),
                    'total' => $cancelledAssignments->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting cancelled tutors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving cancelled tutors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View cancelled tutors page
     */
    public function viewCancelledTutors(Request $request)
    {
        try {
            $query = TutorAssignment::where('status', 'cancelled')
                ->with(['tutor', 'dailyData'])
                ->orderBy('cancelled_at', 'desc');

            // Filter by date range if provided
            if ($request->filled('start_date')) {
                $query->where('cancelled_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('cancelled_at', '<=', $request->end_date . ' 23:59:59');
            }

            // Filter by tutor name if provided
            if ($request->filled('tutor_search')) {
                $search = $request->tutor_search;
                $query->whereHas('tutor', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('tusername', 'like', "%{$search}%");
                });
            }

            $cancelledAssignments = $query->paginate(20);

            return view('cancelled-tutors.index', compact('cancelledAssignments'));
        } catch (\Exception $e) {
            Log::error('Error loading cancelled tutors view: ' . $e->getMessage());
            return back()->with('error', 'Error loading cancelled tutors: ' . $e->getMessage());
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
