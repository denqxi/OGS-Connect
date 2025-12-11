<?php

namespace App\Http\Controllers;

use App\Models\AssignedDailyData;
use App\Models\ScheduleCancellation;
use App\Models\TutorWorkDetail;
use App\Models\Notification;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ScheduleCancellationController extends Controller
{
    public function cancel(Request $request, $assignmentId)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
            'cancelled_by' => 'required|in:main_tutor,supervisor',
        ]);

        DB::beginTransaction();
        try {
            $assignment = AssignedDailyData::with(['schedule', 'mainTutor', 'backupTutor'])
                ->findOrFail($assignmentId);

            // Check if already cancelled
            if ($assignment->is_cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'This schedule has already been cancelled.'
                ], 400);
            }

            $originalMainTutor = $assignment->main_tutor;
            $cancelledBy = $request->cancelled_by;
            $cancelledById = $cancelledBy === 'main_tutor' 
                ? $assignment->main_tutor 
                : Auth::guard('supervisor')->user()->supID;

            // Create cancellation record
            $cancellation = ScheduleCancellation::create([
                'assignment_id' => $assignment->id,
                'schedule_id' => $assignment->schedule_daily_data_id,
                'original_main_tutor' => $originalMainTutor,
                'backup_tutor_activated' => $assignment->backup_tutor ? true : false,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_by_id' => $cancelledById,
                'cancelled_at' => now(),
            ]);

            // Block payment for the cancelling tutor's work details
            TutorWorkDetail::where('assignment_id', $assignment->id)
                ->where('tutor_id', $originalMainTutor)
                ->update([
                    'payment_blocked' => true,
                    'block_reason' => 'Schedule cancelled by ' . ($cancelledBy === 'main_tutor' ? 'tutor' : 'supervisor'),
                    'status' => 'cancelled'
                ]);

            $originalTutor = Tutor::where('tutorID', $originalMainTutor)->first();
            $schedule = $assignment->schedule;

            // Send notification to the cancelling tutor
            if ($originalTutor) {
                Notification::create([
                    'user_id' => $originalTutor->tutor_id,
                    'user_type' => 'tutor',
                    'type' => 'schedule_cancelled',
                    'title' => 'Schedule Cancelled - Payment Blocked',
                    'message' => "Your class on {$schedule->date} ({$schedule->school} - {$schedule->class}) has been cancelled. Payment for this class has been blocked. Reason: {$request->cancellation_reason}",
                    'icon' => 'fas fa-ban',
                    'color' => 'red',
                    'is_read' => false,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'schedule_id' => $schedule->id,
                        'cancellation_id' => $cancellation->id,
                        'tutor_id' => $originalMainTutor,
                        'payment_blocked' => true
                    ]
                ]);
            }

            // If there's a backup tutor, promote them to main tutor
            if ($assignment->backup_tutor) {
                $backupTutor = Tutor::where('tutorID', $assignment->backup_tutor)->first();
                
                $assignment->update([
                    'main_tutor' => $assignment->backup_tutor,
                    'backup_tutor' => null,
                    'is_cancelled' => true,
                    'cancellation_id' => $cancellation->id,
                    'class_status' => 'partially_assigned',
                ]);

                // Notify backup tutor about promotion
                if ($backupTutor) {
                    Notification::create([
                        'user_id' => $backupTutor->tutor_id,
                        'user_type' => 'tutor',
                        'type' => 'backup_promoted',
                        'title' => 'Promoted to Main Tutor',
                        'message' => "You have been promoted to main tutor for {$schedule->school} - {$schedule->class} on {$schedule->date}. The original tutor cancelled due to an emergency.",
                        'icon' => 'fas fa-arrow-circle-up',
                        'color' => 'blue',
                        'is_read' => false,
                        'data' => [
                            'assignment_id' => $assignment->id,
                            'schedule_id' => $schedule->id,
                            'tutor_id' => $assignment->backup_tutor
                        ]
                    ]);
                }

                // Notify supervisor about cancellation and promotion
                $this->notifySupervisors($assignment, $cancellation, 
                    "Schedule cancelled. Backup tutor ({$backupTutor->full_name}) has been promoted to main tutor.");

                $message = 'Schedule cancelled successfully. Backup tutor has been promoted to main tutor. Payment blocked for cancelling tutor.';
            } else {
                // No backup tutor available
                $assignment->update([
                    'main_tutor' => null,
                    'is_cancelled' => true,
                    'cancellation_id' => $cancellation->id,
                    'class_status' => 'not_assigned',
                ]);

                // Notify supervisor about cancellation
                $this->notifySupervisors($assignment, $cancellation, 
                    "Schedule cancelled with no backup tutor available. Needs urgent reassignment.");

                $message = 'Schedule cancelled. No backup tutor available. Please assign a new tutor urgently. Payment blocked for cancelling tutor.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule cancellation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel schedule. Please try again.'
            ], 500);
        }
    }

    private function notifySupervisors($assignment, $cancellation, $message)
    {
        try {
            $schedule = $assignment->schedule;
            $tutor = Tutor::where('tutorID', $cancellation->original_main_tutor)->first();
            
            // Notify all active supervisors
            $supervisors = Supervisor::where('status', 'active')->get();
            
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->supervisor_id,
                    'user_type' => 'supervisor',
                    'type' => 'schedule_cancellation_alert',
                    'title' => 'Schedule Cancellation Alert',
                    'message' => "{$tutor->full_name} cancelled {$schedule->school} - {$schedule->class} on {$schedule->date}. {$message} Reason: {$cancellation->cancellation_reason}",
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'orange',
                    'is_read' => false,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'schedule_id' => $schedule->id,
                        'cancellation_id' => $cancellation->id,
                        'tutor_id' => $cancellation->original_main_tutor,
                        'backup_activated' => $cancellation->backup_tutor_activated
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify supervisors: ' . $e->getMessage());
        }
    }

    public function assignNewBackup(Request $request, $assignmentId)
    {
        $request->validate([
            'backup_tutor' => 'required|exists:tutors,tutorID',
        ]);

        try {
            $assignment = AssignedDailyData::findOrFail($assignmentId);

            if (!$assignment->is_cancelled) {
                return back()->with('error', 'Can only assign backup to cancelled schedules.');
            }

            $assignment->update([
                'backup_tutor' => $request->backup_tutor,
                'class_status' => 'fully_assigned',
            ]);

            // Notify new backup tutor
            $backupTutor = Tutor::where('tutorID', $request->backup_tutor)->first();
            $schedule = $assignment->schedule;
            
            if ($backupTutor) {
                Notification::create([
                    'user_id' => $backupTutor->tutor_id,
                    'user_type' => 'tutor',
                    'type' => 'backup_assigned',
                    'title' => 'Assigned as Backup Tutor',
                    'message' => "You have been assigned as backup tutor for {$schedule->school} - {$schedule->class} on {$schedule->date}.",
                    'icon' => 'fas fa-user-shield',
                    'color' => 'green',
                    'is_read' => false,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'schedule_id' => $schedule->id,
                        'tutor_id' => $request->backup_tutor
                    ]
                ]);
            }

            return back()->with('success', 'New backup tutor assigned successfully.');

        } catch (\Exception $e) {
            Log::error('Backup tutor assignment failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign backup tutor. Please try again.');
        }
    }
}
