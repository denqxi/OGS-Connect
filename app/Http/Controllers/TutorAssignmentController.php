<?php

namespace App\Http\Controllers;

use App\Models\DailyData;
use App\Models\Tutor;
use App\Models\TutorAssignment;
use App\Services\TutorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            $assignment = TutorAssignment::findOrFail($assignmentId);
            
            // Check if user has permission to remove this assignment
            $this->checkAssignmentPermission($assignment);
            
            DB::beginTransaction();
            
            $assignment->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tutor assignment removed successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error removing tutor assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove tutor assignment: ' . $e->getMessage()
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
            
            $class = DailyData::findOrFail($classId);
            
            // Check permission
            $this->checkClassPermission($class);
            
            DB::beginTransaction();
            
            // Remove existing assignments
            TutorAssignment::where('daily_data_id', $classId)->delete();
            
            // Add main tutors
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
                }
            }
            
            // Add backup tutor if provided
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
                }
            }
            
            // Assign supervisor to the class if not already assigned
            $this->assignSupervisorToClass($class);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tutor assignments saved successfully'
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
}
