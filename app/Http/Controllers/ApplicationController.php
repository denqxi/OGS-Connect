<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Demo;
use App\Models\Tutor;
use App\Models\TutorDetails;
use App\Models\Notification;
use App\Mail\ApplicantPassedMail;
use App\Mail\ApplicantFailedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
        Log::info('=== registerTutor called ===');
        Log::info('Request data:', ['data' => $request->all()]);
        Log::info('Demo ID:', ['id' => $id]);
        
        try {
            // Check if this is from onboarding pass (different field names)
            $isOnboardingPass = $request->has('interviewer'); // Changed from pass_interviewer
            
            Log::info('Is onboarding pass:', ['isOnboardingPass' => $isOnboardingPass]);
            
            if ($isOnboardingPass) {
                // Validation for onboarding pass
                $request->validate([
                    'system_id' => 'required|string',
                    'username' => 'required|string',
                    'company_email' => 'required|email',
                    'interviewer' => 'required|string|max:255',
                    'password' => 'required|string',
                    'notes' => 'nullable|string',
                ]);

                // Try to find onboarding record first
                $onboarding = null;
                $demo = null;
                
                // Check if Onboarding model exists
                if (class_exists(\App\Models\Onboarding::class)) {
                    $onboarding = \App\Models\Onboarding::find($id);
                }
                
                if (!$onboarding) {
                    // Fallback to demo/screening table
                    $demo = Demo::where('id', $id)->first();
                    
                    if (!$demo) {
                        Log::warning('Demo not found with ID: ' . $id);
                        throw new \Exception('Onboarding record not found. Please refresh the page and try again.');
                    }
                    
                    Log::info('Demo found (fallback):', ['demo' => $demo->toArray()]);
                    
                    if (isset($demo->phase) && $demo->phase !== 'onboarding') {
                        throw new \Exception('This record is not in onboarding phase. Current phase: ' . $demo->phase);
                    }
                } else {
                    Log::info('Onboarding found:', ['onboarding' => $onboarding->toArray()]);
                    // Get the demo data through relationships
                    $demo = $onboarding;
                }

                // Check if tutor with this email already exists
                $existingTutor = Tutor::where('email', $request->company_email)->first();
                if ($existingTutor) {
                    throw new \Exception('A tutor with email ' . $request->company_email . ' already exists (Tutor ID: ' . $existingTutor->tutorID . '). Please use a different email.');
                }
                
                // Check if tutor with this username already exists
                $existingUsername = Tutor::where('username', $request->username)->first();
                if ($existingUsername) {
                    throw new \Exception('A tutor with username ' . $request->username . ' already exists (Tutor ID: ' . $existingUsername->tutorID . '). Please use a different username.');
                }

                // Use database transaction to ensure data integrity
                $tutor = DB::transaction(function () use ($demo, $request, $id, $onboarding) {
                    // Use system ID from request (already generated from frontend)
                    $systemId = $request->system_id;
                    
                    // Use username and email from request
                    $username = $request->username;
                    $tutorEmail = $request->company_email;
                    $defaultPassword = $request->password;

                    // Get applicant and account info
                    $applicant = $demo->applicant ?? null;
                    $account = $demo->account ?? null;
                    
                    $tutor = Tutor::create([
                        'tutorID' => $systemId,
                        'applicant_id' => $demo->applicant_id ?? null,
                        'account_id' => $demo->account_id ?? null,
                        'email' => $tutorEmail,
                        'password' => Hash::make($defaultPassword),
                        'username' => $username,
                        'status' => 'active',
                        'hire_date_time' => now()
                    ]);
                    
                    Log::info('Tutor created:', ['tutor' => $tutor->toArray()]);
                    
                    // Create notification for successful tutor registration
                    $tutorName = $applicant 
                        ? trim($applicant->first_name . ' ' . $applicant->last_name)
                        : trim(($demo->first_name ?? '') . ' ' . ($demo->last_name ?? ''));
                        
                    $this->createNotification(
                        'success',
                        'New Tutor Registered',
                        "{$tutorName} has been successfully registered as a tutor.",
                        'fas fa-user-plus',
                        'green'
                    );

                    // Note: tutor_accounts table was removed in consolidation migration
                    // Availability data is now stored in work_preferences table (linked via applicant_id)
                    // The work_preferences record should already exist from the applicant's onboarding phase

                    // TODO: Create tutor details record with comprehensive data from demos table
                    // Commented out until tutor_details table is created
                    // $tutorDetail = TutorDetails::create([
                    //     'tutor_id' => $tutor->tutorID,
                    //     'address' => $demo->address,
                    //     'esl_experience' => $demo->esl_experience,
                    //     'work_setup' => $this->mapWorkSetup($demo->work_type),
                    //     'first_day_teaching' => $demo->interview_time ? $demo->interview_time->format('Y-m-d') : now()->addDays(7),
                    //     'educational_attainment' => $this->mapEducation($demo->education),
                    //     'additional_notes' => $this->buildAdditionalNotes($demo, $request->notes)
                    // ]);
                    
                    // Log::info('TutorDetail created:', ['tutorDetail' => $tutorDetail->toArray()]);

                    // Send email with employee credentials
                    $applicantEmail = $applicant ? $applicant->email : $demo->email;
                    if ($applicantEmail) {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantPassedMail(
                                $tutorName,
                                $applicantEmail,
                                'onboarding',
                                null, // no next phase, they're now hired
                                null, // no next schedule
                                $request->interviewer,
                                $request->notes,
                                $tutorEmail, // company email
                                $defaultPassword // temporary password
                            ));
                            Log::info('Employee credentials email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send employee credentials email: ' . $e->getMessage());
                            // Don't fail the registration if email fails
                        }
                    }

                    // Delete the onboarding/demo record since the tutor is now registered
                    if ($onboarding) {
                        $onboarding->delete();
                        Log::info('Onboarding record deleted after successful tutor registration');
                    } else {
                        $demo->delete();
                        Log::info('Demo record deleted after successful tutor registration');
                    }

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

                $demo = Demo::where('id', $id)->first();
                
                if (!$demo) {
                    Log::warning('Demo not found with ID: ' . $id);
                    throw new \Exception('Demo record not found. Please refresh the page and try again.');
                }
                
                if ($demo->status === 'hired') {
                    throw new \Exception('This demo has already been hired.');
                }

                // Check if tutor with this email already exists
                $existingTutor = Tutor::where('email', $request->personal_email)->first();
                if ($existingTutor) {
                    throw new \Exception('A tutor with email ' . $request->personal_email . ' already exists (Tutor ID: ' . $existingTutor->tutorID . '). Please use a different email or contact the administrator.');
                }

                // Generate tutorID
                $systemId = Tutor::generateFormattedId();
                
                // Use company email if provided, otherwise generate from username
                $companyEmail = $request->has('company_email') && $request->company_email
                    ? $request->company_email
                    : Tutor::generateCompanyEmail($request->username);

                $tutor = Tutor::create([
                    'tutorID' => $systemId,
                    'applicant_id' => $demo->applicant_id ?? null,
                    'account_id' => $demo->account_id ?? null,
                    'email' => $companyEmail,
                    'password' => Hash::make($request->password),
                    'username' => $request->username,
                    'status' => 'active',
                    'hire_date_time' => now()
                ]);

                // Delete the demo record since the tutor is now registered
                $demo->delete();
                Log::info('Demo record deleted after successful tutor registration (non-onboarding)');
            }

            Log::info('Tutor registration completed successfully');
            
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
            Log::error('Tutor registration error: ' . $e->getMessage());
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
            Log::error('Username generation error: ' . $e->getMessage());
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
            $currentResults = $demo->results;
            $currentPhase = $demo->phase;

            // Handle phase update (moving between phases)
            if ($request->has('phase')) {
                $newPhase = $request->phase;
                
                // Special handling for onboarding phase - move to onboarding table
                if ($newPhase === 'onboarding') {
                    // Get the current supervisor
                    $supervisorId = Auth::guard('supervisor')->check() 
                        ? Auth::guard('supervisor')->user()->supervisor_id 
                        : $demo->supervisor_id;
                    
                    // Create onboarding record - check which columns exist
                    $onboardingData = [
                        'applicant_id' => $demo->applicant_id,
                        'account_id' => $demo->account_id,
                        'phase' => 'onboarding',
                        'notes' => $request->notes ?? 'Passed demo - moved to onboarding',
                    ];
                    
                    // Only add columns if they exist in the table
                    if (Schema::hasColumn('onboardings', 'assessed_by')) {
                        $onboardingData['assessed_by'] = $supervisorId;
                    }
                    
                    if (Schema::hasColumn('onboardings', 'onboarding_date_time')) {
                        $onboardingData['onboarding_date_time'] = $request->next_schedule ?? $demo->screening_date_time ?? now();
                    }
                    
                    $onboarding = \App\Models\Onboarding::create($onboardingData);
                    
                    // Sync with applicant's interview_time
                    if ($demo->applicant && ($request->next_schedule ?? $demo->screening_date_time)) {
                        $demo->applicant->update(['interview_time' => $request->next_schedule ?? $demo->screening_date_time]);
                    }
                    
                    // Delete from screening table
                    $demo->delete();
                    
                    // Send email notification to applicant
                    $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                    if ($applicantEmail) {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantPassedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $currentPhase, // phase they just passed
                                'onboarding', // next phase
                                $request->next_schedule ?? $demo->screening_date_time,
                                $supervisorId ? (\App\Models\Supervisor::find($supervisorId)->full_name ?? 'Supervisor') : 'Supervisor',
                                $request->notes ?? 'Congratulations on passing!'
                            ));
                            Log::info('Pass notification email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send pass notification email: ' . $e->getMessage());
                        }
                    }
                    
                    // Create notification for phase change
                    $this->createNotification(
                        'success',
                        'Moved to Onboarding',
                        "Application for {$demo->first_name} {$demo->last_name} has been moved to onboarding phase.",
                        'fas fa-check-circle',
                        'green',
                        [
                            'onboarding_id' => $onboarding->onboarding_id,
                            'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                            'old_phase' => $currentPhase,
                            'new_phase' => 'onboarding',
                            'assigned_account' => $demo->assigned_account,
                            'updated_at' => now()->toISOString()
                        ]
                    );
                } else {
                    // Regular phase update within screening table
                    $updateData = [
                        'phase' => $newPhase,
                    ];
                    
                    // Update notes if provided
                    if ($request->has('notes') && $request->notes) {
                        $updateData['notes'] = $request->notes;
                    }
                    
                    // Update schedule if provided and sync with applicant's interview_time
                    if ($request->has('next_schedule') && $request->next_schedule) {
                        $updateData['screening_date_time'] = $request->next_schedule;
                        // Sync with applicant's interview_time
                        if ($demo->applicant) {
                            $demo->applicant->update(['interview_time' => $request->next_schedule]);
                        }
                    }
                    
                    // Update results if provided
                    if ($request->has('results')) {
                        $updateData['results'] = $request->results;
                    }
                    
                    $demo->update($updateData);
                    
                    // Send email notification to applicant when moving forward
                    $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                    if ($applicantEmail && $newPhase !== 'archive') {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantPassedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $currentPhase, // phase they just passed
                                $newPhase, // next phase
                                $request->next_schedule,
                                Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : 'Supervisor',
                                $request->notes ?? "Congratulations! You've been moved to the {$newPhase} phase."
                            ));
                            Log::info('Pass notification email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send pass notification email: ' . $e->getMessage());
                        }
                    }
                    
                    // Create notification for phase change
                    $this->createNotification(
                        'success',
                        'Phase Changed - Moved to ' . ucfirst($newPhase),
                        "Application for {$demo->first_name} {$demo->last_name} has been moved to {$newPhase} phase.",
                        'fas fa-check-circle',
                        'green',
                        [
                            'demo_id' => $demo->id,
                            'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                            'old_phase' => $currentPhase,
                            'new_phase' => $newPhase,
                            'results' => $updateData['results'] ?? $currentResults,
                            'assigned_account' => $demo->assigned_account,
                            'updated_at' => now()->toISOString()
                        ]
                    );
                }
            }
            // Handle results/status update
            elseif ($request->has('status') || $request->has('results')) {
                $newResults = $request->status ?? $request->results;
                $updateData = [
                    'results' => $newResults,
                ];
                
                // Update notes if provided
                if ($request->has('notes') && $request->notes) {
                    $updateData['notes'] = $request->notes;
                }
                
                // Update schedule if provided and sync with applicant's interview_time
                if ($request->has('next_schedule') && $request->next_schedule) {
                    $updateData['screening_date_time'] = $request->next_schedule;
                    // Sync with applicant's interview_time
                    if ($demo->applicant) {
                        $demo->applicant->update(['interview_time' => $request->next_schedule]);
                    }
                }
                
                $demo->update($updateData);
                
                // Send email notification based on results
                $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                if ($applicantEmail) {
                    try {
                        if ($newResults === 'passed') {
                            // Send pass email
                            Mail::to($applicantEmail)->send(new ApplicantPassedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $currentPhase,
                                null, // staying in same phase, just updating results
                                $request->next_schedule,
                                Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : 'Supervisor',
                                $request->notes ?? "Congratulations! You've passed the {$currentPhase} phase."
                            ));
                            Log::info('Results pass email sent to: ' . $applicantEmail);
                        } elseif ($newResults === 'failed') {
                            // Send fail email
                            Mail::to($applicantEmail)->send(new ApplicantFailedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $currentPhase,
                                'not_recommended',
                                null,
                                Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : 'Supervisor',
                                $request->notes ?? "Unfortunately, you did not pass the {$currentPhase} phase."
                            ));
                            Log::info('Results fail email sent to: ' . $applicantEmail);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send results update email: ' . $e->getMessage());
                    }
                }
                
                // Create notification for results change
                $this->createNotification(
                    $newResults === 'passed' ? 'success' : ($newResults === 'failed' ? 'error' : 'info'),
                    'Results Updated',
                    "Results for {$demo->first_name} {$demo->last_name} has been updated to: " . ucfirst(str_replace('_', ' ', $newResults)) . ".",
                    $newResults === 'passed' ? 'fas fa-check-circle' : ($newResults === 'failed' ? 'fas fa-times-circle' : 'fas fa-info-circle'),
                    $newResults === 'passed' ? 'green' : ($newResults === 'failed' ? 'red' : 'blue'),
                    [
                        'demo_id' => $demo->id,
                        'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                        'old_results' => $currentResults,
                        'new_results' => $newResults,
                        'assigned_account' => $demo->assigned_account,
                        'updated_at' => now()->toISOString()
                    ]
                );
            }
            // Handle next step update
            elseif ($request->has('next_status')) {
                $request->validate([
                    'next_status' => 'required|string',
                    'next_schedule' => 'nullable|date',
                    'notes' => 'nullable|string'
                ]);

                $nextPhase = $request->next_status;
                $updateData = [
                    'phase' => $nextPhase,
                ];
                
                // Update schedule if provided and sync with applicant's interview_time
                if ($request->next_schedule) {
                    $updateData['screening_date_time'] = $request->next_schedule;
                    // Sync with applicant's interview_time
                    if ($demo->applicant) {
                        $demo->applicant->update(['interview_time' => $request->next_schedule]);
                    }
                }
                
                // Update notes if provided
                if ($request->notes) {
                    $updateData['notes'] = $request->notes;
                }

                $demo->update($updateData);
                
                // Send email notification for phase progression
                $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                if ($applicantEmail) {
                    try {
                        Mail::to($applicantEmail)->send(new ApplicantPassedMail(
                            $demo->first_name . ' ' . $demo->last_name,
                            $applicantEmail,
                            $currentPhase,
                            $nextPhase,
                            $request->next_schedule,
                            Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : 'Supervisor',
                            $request->notes ?? "Congratulations! You've progressed to the {$nextPhase} phase."
                        ));
                        Log::info('Phase progression email sent to: ' . $applicantEmail);
                    } catch (\Exception $e) {
                        Log::error('Failed to send phase progression email: ' . $e->getMessage());
                    }
                }
                
                // Create notification for phase progression
                $this->createNotification(
                    'info',
                    'Progress - Moved to ' . ucfirst($nextPhase),
                    "Application for {$demo->first_name} {$demo->last_name} has progressed to {$nextPhase} phase" . ($request->next_schedule ? " scheduled for " . \Carbon\Carbon::parse($request->next_schedule)->format('M j, Y \a\t g:i A') : '') . ".",
                    'fas fa-arrow-right',
                    'blue',
                    [
                        'demo_id' => $demo->id,
                        'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                        'old_phase' => $currentPhase,
                        'new_phase' => $nextPhase,
                        'assigned_account' => $demo->assigned_account,
                        'schedule' => $request->next_schedule,
                        'updated_at' => now()->toISOString()
                    ]
                );
            }
            // Handle general updates (notes, schedule, etc.)
            else {
                $updateData = [];
                
                if ($request->has('notes')) {
                    $updateData['notes'] = $request->notes;
                }
                
                if ($request->has('screening_date_time') || $request->has('schedule')) {
                    $newSchedule = $request->screening_date_time ?? $request->schedule;
                    $updateData['screening_date_time'] = $newSchedule;
                    // Sync with applicant's interview_time
                    if ($demo->applicant) {
                        $demo->applicant->update(['interview_time' => $newSchedule]);
                    }
                }
                
                if (!empty($updateData)) {
                    $demo->update($updateData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Demo update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating: ' . $e->getMessage()
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
            
            if ($demo->phase !== 'demo') {
                throw new \Exception('Only demo phase applications can be finalized.');
            }

            $newPhase = $request->status === 'success' ? 'onboarding' : 'screening';
            $newResults = $request->status === 'success' ? 'passed' : 'failed';
            
            $demo->update([
                'phase' => $newPhase,
                'results' => $newResults,
                'notes' => $request->notes
            ]);
            
            // Create notification for demo finalization
            $this->createNotification(
                $request->status === 'success' ? 'success' : 'error',
                $request->status === 'success' ? 'Demo Passed - Moved to Onboarding' : 'Demo Failed',
                "Demo for {$demo->first_name} {$demo->last_name} has been " . ($request->status === 'success' ? 'passed and moved to onboarding' : 'failed') . ".",
                $request->status === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle',
                $request->status === 'success' ? 'green' : 'red',
                [
                    'demo_id' => $demo->id,
                    'applicant_name' => $demo->first_name . ' ' . $demo->last_name,
                    'phase' => $newPhase,
                    'results' => $newResults,
                    'assigned_account' => $demo->assigned_account,
                    'updated_at' => now()->toISOString()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Demo finalized successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Demo finalization error: ' . $e->getMessage());
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
                    // Keep current status, update interview time and screening schedule
                    $newInterviewTime = $request->input('new_interview_time');
                    
                    // Update screening schedule
                    $demo->update([
                        'screening_date_time' => $newInterviewTime,
                        'notes' => $notes
                    ]);
                    
                    // Sync with applicant's interview_time
                    if ($demo->applicant && $newInterviewTime) {
                        $demo->applicant->update(['interview_time' => $newInterviewTime]);
                    }
                    
                    // Send email notification for missed interview
                    $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                    if ($applicantEmail) {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantFailedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $demo->phase,
                                'no_answer',
                                $newInterviewTime,
                                $interviewer,
                                $notes
                            ));
                            Log::info('Missed interview email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send missed interview email: ' . $e->getMessage());
                        }
                    }
                    
                    // Create notification for missed demo/onboarding
                    $phaseName = ($demo->phase === 'onboarding' || $demo->status === 'onboarding') ? 'Onboarding' : 'Demo';
                    $this->createNotification(
                        'warning',
                        $phaseName . ' Missed - Rescheduled',
                        "{$phaseName} for {$demo->first_name} {$demo->last_name} was missed and has been rescheduled" . ($newInterviewTime ? " to " . \Carbon\Carbon::parse($newInterviewTime)->format('M j, Y \a\t g:i A') : '') . ".",
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
                    // Send email notification before archiving
                    $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                    if ($applicantEmail) {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantFailedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $demo->phase,
                                $failReason,
                                null, // no reschedule
                                $interviewer,
                                $notes
                            ));
                            Log::info('Application status email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send application status email: ' . $e->getMessage());
                        }
                    }
                    
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
                            'status' => $failReason,
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
                    
                    // Find the account by account_name to get account_id
                    $account = \App\Models\Account::where('account_name', $transferData['assigned_account'])->first();
                    
                    if (!$account) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid account name: ' . $transferData['assigned_account']
                        ], 400);
                    }
                    
                    // Store old account before update
                    $oldAccountName = $demo->assigned_account;
                    
                    $demo->update([
                        'account_id' => $account->account_id,
                        'phase' => $transferData['new_status'],
                        'screening_date_time' => $transferData['schedule'],
                        'notes' => $notes
                    ]);
                    
                    // Sync with applicant's interview_time
                    if ($demo->applicant && $transferData['schedule']) {
                        $demo->applicant->update(['interview_time' => $transferData['schedule']]);
                    }
                    
                    // Refresh the model to load the new account relationship
                    $demo->refresh();
                    $demo->load('account');
                    
                    // Send email notification for account transfer with new schedule
                    $applicantEmail = $demo->applicant ? $demo->applicant->email : $demo->email;
                    if ($applicantEmail && $transferData['schedule']) {
                        try {
                            Mail::to($applicantEmail)->send(new ApplicantFailedMail(
                                $demo->first_name . ' ' . $demo->last_name,
                                $applicantEmail,
                                $demo->phase,
                                're_schedule',
                                $transferData['schedule'],
                                $interviewer,
                                $notes ?? "Your application has been transferred to {$transferData['assigned_account']} account."
                            ));
                            Log::info('Account transfer email sent to: ' . $applicantEmail);
                        } catch (\Exception $e) {
                            Log::error('Failed to send account transfer email: ' . $e->getMessage());
                        }
                    }
                    
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
                            'old_account' => $oldAccountName,
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
            Log::error('Demo failure handling error: ' . $e->getMessage());
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
        $payload = $demo->toArray();
        $payload['final_status'] = $finalStatus;
        $payload['interviewer'] = $interviewer;
        $payload['notes'] = $notes ?? '';

        // Determine archive_by (supervisor)
        $archiveBy = auth()->guard('supervisor')->check() 
            ? auth()->guard('supervisor')->user()->supervisor_id 
            : session('supervisor_id');

        \App\Models\Archive::create([
            'applicant_id' => $demo->applicant_id ?? null,
            'archive_by' => $archiveBy,
            'notes' => $notes ?? '',
            'archive_date_time' => now(),
            'category' => 'demo',
            'status' => $finalStatus,
            'payload' => $payload,
        ]);
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

    /**
     * Generate next incremental tutor ID
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateTutorId()
    {
        try {
            $tutorID = Tutor::generateFormattedId();
            
            return response()->json([
                'success' => true,
                'tutorID' => $tutorID,
                'message' => 'Tutor ID generated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating tutor ID:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to generate tutor ID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique username based on applicant's name
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Onboarding ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUniqueUsername(Request $request, $id)
    {
        try {
            // Get onboarding or demo record
            $onboarding = \App\Models\Onboarding::find($id);
            
            if (!$onboarding) {
                return response()->json([
                    'error' => 'Onboarding record not found'
                ], 404);
            }

            // Get applicant information
            $applicant = $onboarding->applicant;
            if (!$applicant) {
                return response()->json([
                    'error' => 'Applicant not found'
                ], 404);
            }

            $firstName = strtolower(preg_replace('/[^a-zA-Z]/', '', $applicant->first_name));
            $lastName = strtolower(preg_replace('/[^a-zA-Z]/', '', $applicant->last_name));
            
            // Generate base username from first name + last name
            $baseUsername = $firstName . $lastName;
            
            // Get current username from request to avoid duplicating it
            $currentUsername = $request->input('current_username');
            
            // Check if username exists and add counter if needed
            $username = $baseUsername;
            $counter = 1;
            
            // Keep incrementing until we find an available username
            // that's also different from the current one
            while (Tutor::where('username', $username)->exists() || $username === $currentUsername) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            return response()->json([
                'success' => true,
                'username' => $username,
                'message' => 'Username generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating username:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to generate username: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique company email based on username or applicant's name
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Onboarding ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUniqueEmail(Request $request, $id)
    {
        try {
            // Get current email from request to avoid duplicating it
            $currentEmail = $request->input('current_email');
            
            // Get current username from request or generate from name
            $currentUsername = $request->input('username');
            
            if ($currentUsername) {
                // If username is provided, use it as base for email
                $baseEmail = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUsername));
            } else {
                // Otherwise, get from onboarding record
                $onboarding = \App\Models\Onboarding::find($id);
                
                if (!$onboarding) {
                    return response()->json([
                        'error' => 'Onboarding record not found'
                    ], 404);
                }

                $applicant = $onboarding->applicant;
                if (!$applicant) {
                    return response()->json([
                        'error' => 'Applicant not found'
                    ], 404);
                }

                $firstName = strtolower(preg_replace('/[^a-zA-Z]/', '', $applicant->first_name));
                $lastName = strtolower(preg_replace('/[^a-zA-Z]/', '', $applicant->last_name));
                $baseEmail = $firstName . $lastName;
            }
            
            // Generate unique email
            $email = $baseEmail . '@ogsconnect.com';
            $counter = 1;
            
            // Keep incrementing until we find an available email
            // that's also different from the current one
            while (Tutor::where('email', $email)->exists() || $email === $currentEmail) {
                $email = $baseEmail . $counter . '@ogsconnect.com';
                $counter++;
            }

            return response()->json([
                'success' => true,
                'email' => $email,
                'message' => 'Email generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating email:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to generate email: ' . $e->getMessage()
            ], 500);
        }
    }
}
