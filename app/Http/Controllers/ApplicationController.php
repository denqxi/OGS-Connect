<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Demo;
use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\TutorDetails;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    /**
     * Register a new tutor from a demo application
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerTutor(Request $request, $id)
    {
        \Log::info('=== registerTutor called ===');
        \Log::info('Request data:', ['data' => $request->all()]);
        \Log::info('Demo ID:', ['id' => $id]);
        
        try {
            // Check if this is from onboarding pass (different field names)
            $isOnboardingPass = $request->has('interviewer'); // Changed from pass_interviewer
            
            \Log::info('Is onboarding pass:', ['isOnboardingPass' => $isOnboardingPass]);
            
            if ($isOnboardingPass) {
                // Validation for onboarding pass
                $request->validate([
                    'interviewer' => 'required|string|max:255',
                    'password' => 'required|string',
                    'notes' => 'nullable|string',
                ]);

                $demo = Demo::findOrFail($id);
                \Log::info('Demo found:', ['demo' => $demo->toArray()]);
                
                if ($demo->status === 'hired') {
                    throw new \Exception('This demo has already been hired.');
                }

                // Use database transaction to ensure data integrity
                $tutor = DB::transaction(function () use ($demo, $request, $id) {
                    // Generate system ID (tutorID) using the Tutor model method - OGS-T0001 format
                    $systemId = Tutor::generateFormattedId();
                    $username = Tutor::generateUsername($demo->first_name, $demo->last_name);
                    
                    // Get tutor data from demo
                    $tutorEmail = $demo->email;
                    $defaultPassword = $request->password; // Use password from form

                    $tutor = Tutor::create([
                        'tutorID' => $systemId,
                        'first_name' => $demo->first_name,
                        'last_name' => $demo->last_name,
                        'email' => $tutorEmail,
                        'tpassword' => Hash::make($defaultPassword),
                        'tusername' => $username,
                        'phone_number' => $demo->contact_number,
                        'sex' => null, // Will be filled later if needed
                        'status' => 'active'
                    ]);
                    
                    \Log::info('Tutor created:', ['tutor' => $tutor->toArray()]);
                    
                    // Create notification for successful tutor registration
                    $tutorName = trim($demo->first_name . ' ' . $demo->last_name);
                    $this->createNotification(
                        'success',
                        'New Tutor Registered',
                        "{$tutorName} has been successfully registered as a tutor and is ready for onboarding.",
                        'fas fa-user-plus',
                        'green'
                    );

                    // Create tutor account record with time availability from demos table
                    $tutorAccount = TutorAccount::create([
                        'tutor_id' => $tutor->tutorID, // Use tutorID as foreign key
                        'account_name' => $demo->assigned_account,
                        'account_number' => null, // Will be filled later
                        'username' => $username,
                        'screen_name' => $tutorName,
                        'available_days' => $demo->days ?? [],
                        'available_times' => [
                            'start_time' => $demo->start_time,
                            'end_time' => $demo->end_time,
                            'interview_time' => $demo->interview_time ? $demo->interview_time->format('Y-m-d H:i:s') : null,
                            'demo_schedule' => $demo->demo_schedule ? $demo->demo_schedule->format('Y-m-d H:i:s') : null
                        ],
                        'preferred_time_range' => $this->determineTimeRange($demo->start_time, $demo->end_time),
                        'timezone' => 'UTC',
                        'availability_notes' => $demo->notes,
                        'status' => 'active'
                    ]);
                    
                    \Log::info('TutorAccount created:', ['tutorAccount' => $tutorAccount->toArray()]);

                    // Create tutor details record with comprehensive data from demos table
                    $tutorDetail = TutorDetails::create([
                        'tutor_id' => $tutor->tutorID, // Use tutorID as foreign key
                        'address' => $demo->address,
                        'esl_experience' => $demo->esl_experience,
                        'work_setup' => $this->mapWorkSetup($demo->work_type),
                        'first_day_teaching' => $demo->interview_time ? $demo->interview_time->format('Y-m-d') : now()->addDays(7),
                        'educational_attainment' => $this->mapEducation($demo->education),
                        'additional_notes' => $this->buildAdditionalNotes($demo, $request->notes)
                    ]);
                    
                    \Log::info('TutorDetail created:', ['tutorDetail' => $tutorDetail->toArray()]);

                    // Delete the demo record since the tutor is now registered
                    $demo->delete();
                    \Log::info('Demo record deleted after successful tutor registration');

                    return $tutor;
                });

            } else {
                // Original validation for regular registration
                $request->validate([
                    'name' => 'required|string|max:255',
                    'personal_email' => 'required|email|unique:tutors,email',
                    'password' => 'required|min:8',
                    'assigned_account' => 'required',
                    'username' => 'required|unique:tutors,username',
                ]);

                $demo = Demo::findOrFail($id);
                
                if ($demo->status === 'hired') {
                    throw new \Exception('This demo has already been hired.');
                }

                $tutor = Tutor::create([
                    'name' => $request->name,
                    'email' => $request->personal_email,
                    'password' => Hash::make($request->password),
                    'username' => $request->username,
                    'assigned_account' => $request->assigned_account,
                    'status' => 'active',
                    'demo_id' => $id,
                    'registered_at' => now()
                ]);

                // Delete the demo record since the tutor is now registered
                $demo->delete();
                \Log::info('Demo record deleted after successful tutor registration (non-onboarding)');
            }

            \Log::info('Tutor registration completed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Tutor registered successfully',
                'tutor' => [
                    'username' => $tutor->username,
                    'name' => $tutor->name,
                    'email' => $tutor->email
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tutor registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error registering tutor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate the next available username for tutor registration
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUsername($id)
    {
        try {
            $demo = Demo::findOrFail($id);
            $systemId = Tutor::generateFormattedId(); // Generate system ID (tutorID)
            $username = Tutor::generateUsername($demo->first_name, $demo->last_name);
            
            return response()->json([
                'success' => true,
                'system_id' => $systemId, // Return system ID instead of unique_id
                'username' => $username,
                'tutor_name' => trim($demo->first_name . ' ' . $demo->last_name),
                'tutor_email' => $demo->email,
                'assigned_account' => $demo->assigned_account
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Username generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating username: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a demo application
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDemoStatus(Request $request, $id)
    {
        try {
            $demo = Demo::findOrFail($id);
            $currentStatus = $demo->status;

            // Validate the request based on current status
            if ($request->has('status')) {
                // Direct status update (e.g., fail/hired)
                $newStatus = $request->status;
                $demo->update([
                    'status' => $newStatus,
                    'notes' => $request->notes,
                    'finalized_at' => in_array($newStatus, ['hired', 'not_hired']) ? now() : null,
                    'moved_to_onboarding_at' => $newStatus === 'onboarding' ? now() : null
                ]);
                
                // Create notification for status change
                $this->createNotification(
                    $newStatus === 'hired' ? 'success' : ($newStatus === 'not_hired' ? 'error' : 'info'),
                    'Demo Status Updated',
                    "Demo status for {$demo->first_name} {$demo->last_name} has been updated to: " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
                    $newStatus === 'hired' ? 'fas fa-check-circle' : ($newStatus === 'not_hired' ? 'fas fa-times-circle' : 'fas fa-info-circle'),
                    $newStatus === 'hired' ? 'green' : ($newStatus === 'not_hired' ? 'red' : 'blue'),
                    [
                        'demo_id' => $demo->id,
                        'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                        'old_status' => $currentStatus,
                        'new_status' => $newStatus,
                        'assigned_account' => $demo->assigned_account,
                        'updated_at' => now()->toISOString()
                    ]
                );
            } else {
                // Next step update
                $request->validate([
                    'next_status' => 'required|string',
                    'next_schedule' => 'nullable|date',
                    'notes' => 'nullable|string'
                ]);

                $nextStatus = $request->next_status;
                $updateData = [
                    'status' => $nextStatus,
                    'notes' => $request->notes,
                ];

                // Set appropriate schedule based on next status
                if ($nextStatus === 'training') {
                    $updateData['training_schedule'] = $request->next_schedule;
                    $updateData['moved_to_training_at'] = now();
                } elseif ($nextStatus === 'demo') {
                    $updateData['demo_schedule'] = $request->next_schedule;
                    $updateData['moved_to_demo_at'] = now();
                } elseif ($nextStatus === 'onboarding') {
                    $updateData['moved_to_onboarding_at'] = now();
                }

                $demo->update($updateData);
                
                // Create notification for status progression
                $this->createNotification(
                    'info',
                    'Demo Progress - Moved to ' . ucfirst($nextStatus),
                    "Demo for {$demo->first_name} {$demo->last_name} has progressed to {$nextStatus} stage" . ($request->next_schedule ? " scheduled for " . \Carbon\Carbon::parse($request->next_schedule)->format('M j, Y \a\t g:i A') : '') . ".",
                    'fas fa-arrow-right',
                    'blue',
                    [
                        'demo_id' => $demo->id,
                        'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                        'old_status' => $currentStatus,
                        'new_status' => $nextStatus,
                        'assigned_account' => $demo->assigned_account,
                        'schedule' => $request->next_schedule,
                        'updated_at' => now()->toISOString()
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Demo status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize a demo application
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizeDemo(Request $request, $id)
    {
        try {
            $demo = Demo::findOrFail($id);
            
            if ($demo->status !== 'demo') {
                throw new \Exception('Only demo stage applications can be finalized.');
            }

            $newStatus = $request->status === 'success' ? 'onboarding' : 'not_hired';
            $demo->update([
                'status' => $newStatus,
                'finalized_at' => now(),
                'moved_to_onboarding_at' => $request->status === 'success' ? now() : null,
                'notes' => $request->notes
            ]);
            
            // Create notification for demo finalization
            $this->createNotification(
                $request->status === 'success' ? 'success' : 'error',
                $request->status === 'success' ? 'Demo Passed - Moved to Onboarding' : 'Demo Failed - Not Hired',
                "Demo for {$demo->first_name} {$demo->last_name} has been " . ($request->status === 'success' ? 'passed and moved to onboarding' : 'failed and marked as not hired') . ".",
                $request->status === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle',
                $request->status === 'success' ? 'green' : 'red',
                [
                    'demo_id' => $demo->id,
                    'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                    'final_status' => $newStatus,
                    'assigned_account' => $demo->assigned_account,
                    'finalized_at' => now()->toISOString()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Demo finalized successfully'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Demo finalization error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error finalizing demo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle demo failure actions
     */
    public function handleFail(Request $request, $id)
    {
        try {
            $demo = Demo::findOrFail($id);
            $failReason = $request->input('fail_reason');
            $interviewer = $request->input('interviewer');
            $notes = $request->input('notes');

            switch ($failReason) {
                case 'missed':
                    // Keep current status, update interview time
                    $newInterviewTime = $request->input('new_interview_time');
                    $demo->update([
                        'interview_time' => $newInterviewTime,
                        'demo_schedule' => $newInterviewTime, // Also update demo_schedule for table display
                        'interviewer' => $interviewer,
                        'notes' => $notes
                    ]);
                    
                    // Create notification for missed demo
                    $this->createNotification(
                        'warning',
                        'Demo Missed - Rescheduled',
                        "Demo for {$demo->first_name} {$demo->last_name} was missed and has been rescheduled" . ($newInterviewTime ? " to " . \Carbon\Carbon::parse($newInterviewTime)->format('M j, Y \a\t g:i A') : '') . ".",
                        'fas fa-calendar-times',
                        'yellow',
                        [
                            'demo_id' => $demo->id,
                            'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                            'fail_reason' => $failReason,
                            'new_interview_time' => $newInterviewTime,
                            'interviewer' => $interviewer,
                            'updated_at' => now()->toISOString()
                        ]
                    );
                    break;

                case 'declined':
                case 'not_recommended':
                    // Move to archive
                    $this->archiveDemo($demo, $failReason, $interviewer, $notes);
                    
                    // Create notification for archived demo
                    $this->createNotification(
                        'warning',
                        'Demo Archived - ' . ucfirst(str_replace('_', ' ', $failReason)),
                        "Demo for {$demo->first_name} {$demo->last_name} has been archived due to: " . ucfirst(str_replace('_', ' ', $failReason)) . ".",
                        'fas fa-archive',
                        'yellow',
                        [
                            'demo_id' => $demo->id,
                            'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                            'fail_reason' => $failReason,
                            'final_status' => $failReason,
                            'interviewer' => $interviewer,
                            'archived_at' => now()->toISOString()
                        ]
                    );
                    
                    $demo->delete();
                    break;

                case 'transfer_account':
                    // Transfer to different account with new status
                    // Note: start_time and end_time are applicant's preferred schedule, should not be changed
                    $transferData = $request->input('transfer_data');
                    $demo->update([
                        'assigned_account' => $transferData['assigned_account'],
                        'status' => $transferData['new_status'],
                        // Keep original start_time and end_time (applicant's preferred schedule)
                        'interview_time' => $transferData['schedule'],
                        'demo_schedule' => $transferData['schedule'], // Also update demo_schedule for table display
                        'interviewer' => $interviewer,
                        'notes' => $notes
                    ]);
                    
                    // Create notification for account transfer
                    $this->createNotification(
                        'info',
                        'Demo Transferred to Different Account',
                        "Demo for {$demo->first_name} {$demo->last_name} has been transferred to {$transferData['assigned_account']} account with status: " . ucfirst(str_replace('_', ' ', $transferData['new_status'])) . ".",
                        'fas fa-exchange-alt',
                        'blue',
                        [
                            'demo_id' => $demo->id,
                            'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                            'old_account' => $demo->assigned_account,
                            'new_account' => $transferData['assigned_account'],
                            'new_status' => $transferData['new_status'],
                            'schedule' => $transferData['schedule'],
                            'interviewer' => $interviewer,
                            'transferred_at' => now()->toISOString()
                        ]
                    );
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid failure reason'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Failure action processed successfully'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Demo failure handling error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing failure action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive a demo application
     */
    private function archiveDemo(Demo $demo, string $finalStatus, string $interviewer, ?string $notes = null)
    {
        $archivedData = $demo->toArray();
        $archivedData['final_status'] = $finalStatus;
        $archivedData['archived_at'] = now();
        $archivedData['interviewer'] = $interviewer;
        $archivedData['notes'] = $notes ?? '';
        
        // Remove the id to avoid conflicts
        unset($archivedData['id']);
        
        // Remove fields that don't exist in ArchivedApplication
        unset($archivedData['demo_schedule']);
        unset($archivedData['training_schedule']);
        unset($archivedData['moved_to_demo_at']);
        unset($archivedData['moved_to_training_at']);
        unset($archivedData['moved_to_onboarding_at']);
        unset($archivedData['hired_at']);
        unset($archivedData['finalized_at']);
        // Keep assigned_account - it's now in ArchivedApplication
        
        // You might need to create an ArchivedDemo model or use the existing ArchivedApplication
        // For now, I'll assume you want to use ArchivedApplication
        \App\Models\ArchivedApplication::create($archivedData);
    }

    /**
     * Determine time range based on start and end times
     */
    private function determineTimeRange($startTime, $endTime)
    {
        if (!$startTime || !$endTime) {
            return 'flexible';
        }

        $start = \Carbon\Carbon::parse($startTime);
        $hour = $start->hour;

        if ($hour >= 6 && $hour < 12) {
            return 'morning';
        } elseif ($hour >= 12 && $hour < 17) {
            return 'afternoon';
        } elseif ($hour >= 17 && $hour < 22) {
            return 'evening';
        }

        return 'flexible';
    }

    /**
     * Map work setup from demo work_type
     */
    private function mapWorkSetup($workType)
    {
        if (!$workType) {
            return 'WFH';
        }

        $workType = strtolower($workType);
        
        if (str_contains($workType, 'wfh') || str_contains($workType, 'work from home')) {
            return 'WFH';
        } elseif (str_contains($workType, 'was') || str_contains($workType, 'work at site')) {
            return 'WAS';
        } elseif (str_contains($workType, 'hybrid')) {
            return 'Hybrid';
        }

        return 'WFH'; // Default
    }

    /**
     * Map education level from demo education
     */
    private function mapEducation($education)
    {
        if (!$education) {
            return 'Bachelors Degree';
        }

        $education = strtolower($education);
        
        if (str_contains($education, 'high school') || str_contains($education, 'secondary')) {
            return 'High School';
        } elseif (str_contains($education, 'associate') || str_contains($education, 'diploma')) {
            return 'Associate Degree';
        } elseif (str_contains($education, 'bachelor') || str_contains($education, 'bachelor\'s')) {
            return 'Bachelors Degree';
        } elseif (str_contains($education, 'master') || str_contains($education, 'master\'s')) {
            return 'Masters Degree';
        } elseif (str_contains($education, 'doctorate') || str_contains($education, 'phd')) {
            return 'Doctorate';
        }

        return 'Bachelors Degree'; // Default
    }

    /**
     * Build comprehensive additional notes from demo data
     */
    private function buildAdditionalNotes($demo, $formNotes)
    {
        $notes = [];
        
        // Add form notes if provided
        if ($formNotes) {
            $notes[] = "Registration Notes: " . $formNotes;
        }
        
        // Add demo-specific information
        if ($demo->ms_teams) {
            $notes[] = "MS Teams: " . $demo->ms_teams;
        }
        
        if ($demo->resume_link) {
            $notes[] = "Resume Link: " . $demo->resume_link;
        }
        
        if ($demo->intro_video) {
            $notes[] = "Intro Video: " . $demo->intro_video;
        }
        
        if ($demo->speedtest) {
            $notes[] = "Speed Test: " . $demo->speedtest;
        }
        
        if ($demo->main_device) {
            $notes[] = "Main Device: " . $demo->main_device;
        }
        
        if ($demo->backup_device) {
            $notes[] = "Backup Device: " . $demo->backup_device;
        }
        
        if ($demo->source) {
            $notes[] = "Source: " . $demo->source;
        }
        
        if ($demo->referrer_name) {
            $notes[] = "Referrer: " . $demo->referrer_name;
        }
        
        if ($demo->platforms) {
            $platforms = is_array($demo->platforms) ? implode(', ', $demo->platforms) : $demo->platforms;
            $notes[] = "Platforms: " . $platforms;
        }
        
        if ($demo->can_teach) {
            $canTeach = is_array($demo->can_teach) ? implode(', ', $demo->can_teach) : $demo->can_teach;
            $notes[] = "Can Teach: " . $canTeach;
        }
        
        return implode("\n", $notes);
    }

    /**
     * Create a notification
     */
    private function createNotification($type, $title, $message, $icon = null, $color = null, $data = null)
    {
        $defaultIcons = [
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
            'info' => 'fas fa-info-circle'
        ];

        $defaultColors = [
            'success' => 'green',
            'warning' => 'yellow',
            'error' => 'red',
            'info' => 'blue'
        ];

        Notification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon ?? $defaultIcons[$type] ?? 'fas fa-bell',
            'color' => $color ?? $defaultColors[$type] ?? 'blue',
            'data' => $data
        ]);
    }
}