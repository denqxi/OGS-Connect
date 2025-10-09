<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ArchivedApplication;
use App\Models\Demo;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApplicationFormController extends Controller
{
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'address' => 'required|string|max:500',
            'contact_number' => 'required|string|regex:/^09\d{9}$/',
            'email' => 'required|email|max:255',
            'education' => 'required|string|in:shs,college_undergrad,bachelor,master,doctorate',
            'esl_experience' => 'required|string|in:na,1-2,3-4,5plus',
            'resume_link' => 'required|url|max:500',
            'intro_video' => 'required|url|max:500',
            'work_type' => 'required|string|in:work_from_home,work_at_site',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'days' => 'required|array|min:1',
            'platforms' => 'required|array|min:1',
            'can_teach' => 'required|array|min:1',
            'terms_agreement' => 'required|accepted',
        ];

        // Conditional validation for referral
        if ($request->input('source') === 'referral') {
            $rules['referrer_name'] = 'required|string|max:255';
        }

        // Conditional validation for work from home
        if ($request->input('work_type') === 'work_from_home') {
            $rules['speedtest'] = 'required|url|max:500';
            $rules['main_device'] = 'required|url|max:500';
            $rules['backup_device'] = 'required|url|max:500';
        }

        // Add debugging
        Log::info('Form submission attempt', [
            'request_data' => $request->all(),
            'rules' => $rules
        ]);

        // Validate the request
        try {
            $validatedData = $request->validate($rules);
            Log::info('Validation passed', ['validated_data' => $validatedData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            // Return back with validation errors and old input
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }

        // Convert arrays to JSON
        $validatedData['days'] = $request->input('days', []);
        $validatedData['platforms'] = $request->input('platforms', []);
        $validatedData['can_teach'] = $request->input('can_teach', []);
        $validatedData['ms_teams'] = $request->input('ms_teams');
        $validatedData['source'] = $request->input('source');
        $validatedData['referrer_name'] = $request->input('referrer_name');
        $validatedData['interview_time'] = $request->input('interview_time');
        $validatedData['status'] = 'pending'; // Set default status

        try {
            $application = Application::create($validatedData);
            Log::info('Application created successfully', ['application_id' => $application->id]);
            
            // Create notification for new application submission
            $this->createNotification(
                'info',
                'New Application Submitted',
                "A new application has been submitted by {$validatedData['first_name']} {$validatedData['last_name']} ({$validatedData['email']}). Please review the application in the hiring & onboarding section.",
                'fas fa-user-plus',
                'blue',
                [
                    'application_id' => $application->id,
                    'applicant_name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                    'applicant_email' => $validatedData['email'],
                    'submitted_at' => now()->toISOString()
                ]
            );
            
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Application creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $validatedData
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit application. Please try again.'])
                ->withInput();
        }
        
        // Redirect to the submit success page
        Log::info('Redirecting to success page');
        return redirect()->route('application.form.success')->with('success', 'Application Submitted Successfully!');
    }
    
    public function viewTable(Request $request){
        // Check if details view is requested
        if ($request->get('view') === 'details' && $request->get('id')) {
            $application = Application::findOrFail($request->get('id'));
            return view('hiring_onboarding.index', compact('application'));
        }
        
        // Check if archive tab is requested
        if ($request->get('tab') === 'archive') {
            return $this->viewArchive($request);
        }
        
        // Check if demo tab is requested
        if ($request->get('tab') === 'demo') {
            return $this->viewDemo($request);
        }
        
        // Check if onboarding tab is requested
        if ($request->get('tab') === 'onboarding') {
            return $this->viewOnboarding($request);
        }
        
        $query = Application::query();
        
        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) { // Allow search with just 1 character
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('start_time', 'like', "%{$search}%")
                      ->orWhere('end_time', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%");
                });
            }
        }
        
        // Filter by status - partial match
        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if (strlen($status) >= 1) {
                $query->where('status', 'like', "%{$status}%");
            }
        }
        
        // Filter by source - partial match
        if ($request->filled('source')) {
            $source = trim($request->input('source'));
            if (strlen($source) >= 1) {
                $query->where('source', 'like', "%{$source}%");
            }
        }
        
        $applicants = $query->orderBy('created_at', 'asc')->paginate(5);
        
        // Get unique statuses and sources for filter dropdowns
        $statuses = Application::distinct()->pluck('status')->filter()->values();
        $sources = Application::distinct()->pluck('source')->filter()->values();
        
        return view('hiring_onboarding.index', compact('applicants', 'statuses', 'sources'));
    }

    /**
     * Display demo applications
     */
    public function viewDemo(Request $request)
    {
    $query = Demo::query();
    // Exclude onboarding and hired applicants from the For Demo list
    $query->whereNotIn('status', ['onboarding', 'hired']);

        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) { // Allow search with just 1 character
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('assigned_account', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereRaw("DATE_FORMAT(demo_schedule, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("DATE_FORMAT(interview_time, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"]);
                });
            }
        }
        
        // Filter by status - partial match
        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if (strlen($status) >= 1) {
                $query->where('status', 'like', "%{$status}%");
            }
        }
        
        // Filter by assigned account - partial match
        if ($request->filled('account')) {
            $account = trim($request->input('account'));
            if (strlen($account) >= 1) {
                $query->where('assigned_account', 'like', "%{$account}%");
            }
        }
        
        $demos = $query->orderBy('moved_to_demo_at', 'asc')->paginate(5);
        
        // Get unique statuses and accounts for filter dropdowns
        // Exclude 'not_hired' from the status dropdown (not needed)
        $statuses = Demo::distinct()->pluck('status')->filter(function($s) {
            return $s !== 'not_hired';
        })->values();
        $accounts = Demo::distinct()->pluck('assigned_account')->filter()->values();
        
        return view('hiring_onboarding.index', compact('demos', 'statuses', 'accounts'));
         
    }

    
    /**
     * Display archived applications
     */
    public function viewArchive(Request $request)
    {
        $query = ArchivedApplication::query();
        
        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) { // Allow search with just 1 character
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhere('final_status', 'like', "%{$search}%")
                      ->orWhereRaw("DATE_FORMAT(interview_time, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"]);
                });
            }
        }
        
        // Filter by final status - partial match
        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if (strlen($status) >= 1) {
                $query->where('final_status', 'like', "%{$status}%");
            }
        }
        
        $archivedApplicants = $query->orderBy('archived_at', 'asc')->paginate(5);
        
        // Get unique final statuses for filter dropdown
        $statuses = ArchivedApplication::distinct()->pluck('final_status')->filter()->values();
        
        return view('hiring_onboarding.index', compact('archivedApplicants', 'statuses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        return view('hiring_onboarding.applicant-details', compact('application'));
    }

    public function showUneditable(Demo $demo)
    {
        // Return the partial view with data
        return view('hiring_onboarding.applicant-details-unedited', compact('demo'));
    }

    /**
     * Display archived applicant details
     */
    public function showArchived(ArchivedApplication $archivedApplication)
    {
        return view('hiring_onboarding.applicant-details-archived', compact('archivedApplication'));
    }

    /**
     * Handle fail modal submission
     */
    public function handleFail(Request $request, Application $application)
    {
        $specialStatus = $request->input('special_status');
        
        // Dynamic validation based on special status
        $rules = [
            'interviewer' => 'required|string|max:255',
            'special_status' => 'required|string|in:no_answer,re_schedule,declined,not_recommended',
            'notes' => 'nullable|string|max:1000',
        ];
        
        // Require interview_time only for re_schedule
        if ($specialStatus === 're_schedule') {
            $rules['interview_time'] = 'required|date|after:now';
        } else {
            $rules['interview_time'] = 'nullable|date|after:now';
        }
        
        $request->validate($rules);

        $interviewer = $request->input('interviewer');
        $notes = $request->input('notes');
        $interviewTime = $request->input('interview_time');

        $application->interviewer = $interviewer;
        $application->notes = $notes;

        // Handle different statuses based on business logic
        if (in_array($specialStatus, ['declined', 'not_recommended'])) {
            // Move to archive immediately
            $this->archiveApplication($application, $specialStatus);
            
            // Create notification for archived application
            $this->createNotification(
                'warning',
                'Application Archived',
                "Application for {$application->first_name} {$application->last_name} has been archived due to: " . ucfirst(str_replace('_', ' ', $specialStatus)) . ".",
                'fas fa-archive',
                'yellow',
                [
                    'application_id' => $application->id,
                    'applicant_name' => $application->first_name . ' ' . $application->last_name,
                    'final_status' => $specialStatus,
                    'interviewer' => $interviewer,
                    'archived_at' => now()->toISOString()
                ]
            );
            
            $application->delete();
            
            return redirect()->route('hiring_onboarding.index')
                ->with('success', 'Applicant Archived - The applicant record has been successfully moved to the Archive.');
        } 
        elseif ($specialStatus === 'no_answer') {
            // Only increment attempt count for 'no_answer' status
            $application->increment('attempt_count');
            
            // Check if this is the 3rd attempt
            if ($application->attempt_count >= 3) {
                // Move to archive after 3 attempts
                $this->archiveApplication($application, 'no_answer');
                
                // Create notification for archived application after 3 attempts
                $this->createNotification(
                    'warning',
                    'Application Archived - No Answer',
                    "Application for {$application->first_name} {$application->last_name} has been archived after 3 failed contact attempts.",
                    'fas fa-archive',
                    'yellow',
                    [
                        'application_id' => $application->id,
                        'applicant_name' => $application->first_name . ' ' . $application->last_name,
                        'final_status' => 'no_answer',
                        'attempt_count' => $application->attempt_count,
                        'archived_at' => now()->toISOString()
                    ]
                );
                
                $application->delete();
                
                return redirect()->route('hiring_onboarding.index')
                    ->with('success', 'Applicant Archived - The applicant record has been successfully moved to the Archive.');
            } else {
                // Update status to no_answer but keep in database
                $application->status = 'no_answer';
                $application->save();
                
                // Create notification for no answer attempt
                $this->createNotification(
                    'warning',
                    'No Answer - Attempt ' . $application->attempt_count . ' of 3',
                    "No answer from {$application->first_name} {$application->last_name}. Attempt {$application->attempt_count} of 3.",
                    'fas fa-phone-slash',
                    'yellow',
                    [
                        'application_id' => $application->id,
                        'applicant_name' => $application->first_name . ' ' . $application->last_name,
                        'attempt_count' => $application->attempt_count,
                        'status' => 'no_answer'
                    ]
                );
                
                return redirect()->route('hiring_onboarding.applicant.show', $application)
                    ->with('success', 'Application status updated to No Answer. Attempt ' . $application->attempt_count . ' of 3.');
            }
        } 
        elseif ($specialStatus === 're_schedule') {
            // Update status to re_schedule but keep in database
            // Do NOT increment attempt count for re_schedule
            $application->status = 're_schedule';
            
            // Update interview time if provided
            if ($interviewTime) {
                $application->interview_time = $interviewTime;
            }
            
            $application->save();
            
            // Create notification for reschedule
            $this->createNotification(
                'info',
                'Interview Rescheduled',
                "Interview for {$application->first_name} {$application->last_name} has been rescheduled" . ($interviewTime ? " to " . \Carbon\Carbon::parse($interviewTime)->format('M j, Y \a\t g:i A') : '') . ".",
                'fas fa-calendar-alt',
                'blue',
                [
                    'application_id' => $application->id,
                    'applicant_name' => $application->first_name . ' ' . $application->last_name,
                    'new_interview_time' => $interviewTime,
                    'interviewer' => $interviewer,
                    'status' => 're_schedule'
                ]
            );
            
            return redirect()->route('hiring_onboarding.applicant.show', $application)
                ->with('success', 'Application status updated to Re-schedule.');
        }

        return redirect()->back()->with('error', 'Invalid status selected.');
    }

    /**
     * Handle pass modal submission
     */
    public function handlePass(Request $request, Application $application)
    {
        $request->validate([
            'interviewer' => 'required|string|max:255',
            'assigned_account' => 'required|string|in:tutlo,talk915,gl5,babilala',
            'next_status' => 'required|string|in:demo,screening,training',
            'notes' => 'nullable|string|max:1000',
            'demo_schedule' => 'required|date|after:now',
        ]);

        $interviewer = $request->input('interviewer');
        $assignedAccount = $request->input('assigned_account');
        $nextStatus = $request->input('next_status');
        $notes = $request->input('notes');
        $demoSchedule = $request->input('demo_schedule');

        // Move applicant to demo table (preserving original time availability)
        $this->moveToDemo($application, $interviewer, null, null, $assignedAccount, $nextStatus, $notes, $demoSchedule);
        
        // Create notification for passed application
        $this->createNotification(
            'success',
            'Application Passed - Moved to ' . ucfirst($nextStatus),
            "Application for {$application->first_name} {$application->last_name} has passed and been moved to {$nextStatus} stage. Assigned to {$assignedAccount} account.",
            'fas fa-check-circle',
            'green',
            [
                'application_id' => $application->id,
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'new_status' => $nextStatus,
                'assigned_account' => $assignedAccount,
                'interviewer' => $interviewer,
                'demo_schedule' => $demoSchedule,
                'moved_at' => now()->toISOString()
            ]
        );
        
        // Delete from applications table
        $application->delete();

        return redirect()->route('hiring_onboarding.index')
            ->with('success', 'Applicant Passed - The applicant has been moved to the Demo stage.');
    }

    // View Onboarding Participants 
    public function viewOnboarding(Request $request)
    {
        $query = Demo::query();
    
        // ✅ Only show onboarding applicants
        $query->where('status', 'onboarding');
    
        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) { // Allow search with just 1 character
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('assigned_account', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereRaw("DATE_FORMAT(demo_schedule, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("DATE_FORMAT(interview_time, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"]);
                });
            }
        }
    
        // Optional account filter - partial match
        if ($request->filled('account')) {
            $account = trim($request->input('account'));
            if (strlen($account) >= 1) {
                $query->where('assigned_account', 'like', "%{$account}%");
            }
        }
    
        // Sort by onboarding date
        $onboardings = $query->orderBy('moved_to_onboarding_at', 'asc')->paginate(5);
    
        // Dropdown filter options
        $accounts = Demo::distinct()->pluck('assigned_account')->filter()->values();
    
        // ✅ This view will be used for onboarding tab
        return view('hiring_onboarding.index', compact('onboardings', 'accounts'));
    }
    

    /**
     * Archive an application
     */
    private function archiveApplication(Application $application, string $finalStatus)
    {
        $archivedData = $application->toArray();
        $archivedData['final_status'] = $finalStatus;
        $archivedData['archived_at'] = now();
        
        // Remove the id to avoid conflicts
        unset($archivedData['id']);
        
        ArchivedApplication::create($archivedData);
    }

    /**
     * Handle archiving a re-scheduled application
     */
    public function archiveReschedule(Request $request, Application $application)
    {
        $request->validate([
            'interview_time' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $interviewTime = $request->input('interview_time');
        $notes = $request->input('notes');

        // Update interview time and notes
        $application->interview_time = $interviewTime;
        if ($notes) {
            $application->notes = $notes;
        }
        $application->save();

        // Archive the application with reschedule information
        $this->archiveApplication($application, 're_schedule');
        $application->delete();

        return redirect()->route('hiring_onboarding.index')
            ->with('success', 'Re-scheduled application archived with interview time: ' . $interviewTime);
    }

    /**
     * Move an application to demo table
     */
    private function moveToDemo(Application $application, string $interviewer, ?string $startTime, ?string $endTime, string $assignedAccount, string $nextStatus, string $notes = null, $demoSchedule = null)
    {
        $demoData = $application->toArray();
        $demoData['status'] = $nextStatus;
        // Preserve applicant's original preferred time availability
        // $demoData['start_time'] = $startTime; // Don't override applicant's preference
        // $demoData['end_time'] = $endTime; // Don't override applicant's preference
        $demoData['assigned_account'] = $assignedAccount;
        $demoData['interviewer'] = $interviewer;
        $demoData['notes'] = $notes;
        $demoData['demo_schedule'] = $demoSchedule;
        $demoData['moved_to_demo_at'] = now();
        
        // Remove the id to avoid conflicts
        unset($demoData['id']);
        
        Demo::create($demoData);
    }

    /**
     * Get demo data for editing
     */
    public function getDemoEditData($id)
    {
        try {
            $demo = Demo::findOrFail($id);
            
            // Get assignment history for this applicant (all accounts they've been assigned to)
            $assignmentHistory = $this->getAssignmentHistory($demo);
            
            return response()->json([
                'interviewer' => $demo->interviewer,
                'email' => $demo->email,
                'start_time' => $demo->start_time,
                'end_time' => $demo->end_time,
                'assigned_account' => $demo->assigned_account,
                'hiring_status' => $demo->status,
                'schedule' => $demo->demo_schedule,
                'notes' => $demo->notes,
                'assignment_history' => $assignmentHistory,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getDemoEditData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load demo data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignment history for an applicant (all accounts they've been assigned to)
     */
    private function getAssignmentHistory(Demo $demo)
    {
        $assignmentHistory = [];
        
        // Get all demos for this applicant (by email) - current and past
        $demoAccounts = Demo::where('email', $demo->email)
            ->whereNotNull('assigned_account')
            ->pluck('assigned_account')
            ->toArray();
            
        // Get all archived applications for this applicant (by email)
        $archivedAccounts = ArchivedApplication::where('email', $demo->email)
            ->whereNotNull('assigned_account')
            ->pluck('assigned_account')
            ->toArray();
            
        // Combine and get unique accounts
        $assignmentHistory = array_unique(array_merge($demoAccounts, $archivedAccounts));
        
        return array_values($assignmentHistory);
    }

    /**
     * Update demo data
     */
    public function updateDemo(Request $request, Demo $demo)
    {
        $request->validate([
            'interviewer' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'assigned_account' => 'required|string|in:tutlo,talk915,gl5,babilala',
            'hiring_status' => 'required|string|in:screening,training,demo',
            'schedule' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check status progression logic
        $currentStatus = $demo->status;
        $newStatus = $request->input('hiring_status');
        
        // Define allowed status progressions
        $allowedProgressions = [
            'screening' => ['training', 'demo'],
            'training' => ['demo'],
            'demo' => [] // No further progression for now
        ];
        
        // Check if the new status is allowed from current status
        if (!in_array($newStatus, $allowedProgressions[$currentStatus] ?? [])) {
            return redirect()->back()
                ->withErrors(['hiring_status' => 'Cannot change status from ' . ucfirst($currentStatus) . ' to ' . ucfirst($newStatus) . '.'])
                ->withInput();
        }

        $demo->update([
            'interviewer' => $request->input('interviewer'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'assigned_account' => $request->input('assigned_account'),
            'status' => $request->input('hiring_status'),
            'demo_schedule' => $request->input('schedule'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('hiring_onboarding.index', ['tab' => 'demo'])
            ->with('success', 'Demo applicant details updated successfully.');
    }
    // Update the Demo Applicant Details when passed
    public function updateDemoStatus(Request $request, Demo $demo)
    {
        $request->validate([
            'next_status' => 'required|string|in:screening,training,demo',
            'next_schedule' => 'nullable|date|after:now',
            'next_notes' => 'nullable|string|max:1000',
        ]);

        $demo->update([
            'status' => $request->input('next_status'),
            'demo_schedule' => $request->input('next_schedule'),
            'notes' => $request->input('next_notes'),
        ]);

        return redirect()->route('hiring_onboarding.index', ['tab' => 'demo'])
            ->with('success', 'Demo applicant status updated successfully.');
    }

    /**
     * Finalize demo decision (hired/not hired)
     */
    public function finalizeDemo(Request $request, Demo $demo)
    {
        $request->validate([
            'decision' => 'required|string|in:success,failed'
        ]);

        $decision = $request->input('decision');
        
        if ($decision === 'success') {
            // Move to onboarding status
            $demo->update([
                'status' => 'onboarding',
                'finalized_at' => now()
            ]);
            
            return redirect()->route('hiring_onboarding.index', ['tab' => 'demo'])
                ->with('success', 'Applicant has been successfully hired and moved to onboarding!');
        } else {
            // Mark as not hired
            $demo->update([
                'status' => 'not_hired',
                'finalized_at' => now()
            ]);
            
            return redirect()->route('hiring_onboarding.index', ['tab' => 'demo'])
                ->with('success', 'Applicant has been marked as not hired.');
        }
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
