<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Applicant;
use App\Models\Qualification;
use App\Models\Requirement;
use App\Models\Referral;
use App\Models\WorkPreference;
use App\Models\ArchivedApplication;
use App\Models\Demo;
use App\Models\Screening;
use App\Models\Account;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:-18 years|after:-70 years',
            'address' => 'required|string|max:500',
            'contact_number' => 'required|string|regex:/^09\d{9}$/',
            // Ensure unique applicant email to avoid duplicate insert errors
            'email' => 'required|email|max:255|unique:applicants,email',
            'ms_teams' => 'nullable|string|max:255',
            'education' => 'required|string|in:shs,college_undergrad,bachelor,master,doctorate',
            'esl_experience' => 'required|string|in:na,1-2,3-4,5plus',
            'resume_link' => 'required|url|max:500',
            'intro_video' => 'required|url|max:500',
            'work_type' => 'required|string|in:work_from_home,work_at_site',
            'source' => 'required|string|in:fb_boosting,referral',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'interview_time' => 'required|date|after:now',
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

        // Custom validation messages
        $messages = [
            'birth_date.before' => 'You must be at least 18 years old to apply.',
            'birth_date.after' => 'Birth date must be within the last 70 years.',
            'birth_date.date' => 'Please enter a valid birth date.',
            'interview_time.after' => 'Interview time must be in the future.',
            'interview_time.required' => 'Please select your preferred interview time.',
            'contact_number.regex' => 'Contact number must be a valid Philippine mobile number (e.g., 09123456789).',
            'email.unique' => 'This email address has already been used for an application.',
            'terms_agreement.accepted' => 'You must agree to the Terms and Conditions to submit your application.',
        ];

        // Validate the request
        try {
            $validatedData = $request->validate($rules, $messages);
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

        // Convert terms_agreement from "on" to boolean
        $termsAgreement = $request->has('terms_agreement') ? true : false;
        
        // Additional validation for terms agreement
        if (!$termsAgreement) {
            return redirect()->back()
                ->withErrors(['terms_agreement' => 'You must agree to the Terms and Conditions.'])
                ->withInput();
        }

        try {
            // Use database transaction to ensure data integrity
            $application = DB::transaction(function () use ($request, $validatedData, $termsAgreement) {
                // 1. Create Applicant
                $applicant = Applicant::create([
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'middle_name' => $request->input('middle_name'),
                    'birth_date' => $validatedData['birth_date'],
                    'address' => $validatedData['address'],
                    'contact_number' => $validatedData['contact_number'],
                    'email' => $validatedData['email'],
                    'ms_teams' => $request->input('ms_teams') ?: '',
                    'interview_time' => $validatedData['interview_time'],
                ]);

                // 2. Create Qualification
                Qualification::create([
                    'applicant_id' => $applicant->applicant_id,
                    'education' => $validatedData['education'],
                    'esl_experience' => $validatedData['esl_experience'],
                ]);

                // 3. Create Requirement
                Requirement::create([
                    'applicant_id' => $applicant->applicant_id,
                    'resume_link' => $validatedData['resume_link'],
                    'intro_video' => $validatedData['intro_video'],
                    'work_type' => $validatedData['work_type'],
                    'speedtest' => $request->input('speedtest'),
                    'main_devices' => $request->input('main_device'),
                    'backup_devices' => $request->input('backup_device'),
                ]);

                // 4. Create Referral
                Referral::create([
                    'applicant_id' => $applicant->applicant_id,
                    'source' => $request->input('source', 'other'),
                    'referrer_name' => $request->input('referrer_name'),
                ]);

                // 5. Create WorkPreference
                // Convert time strings to proper time format (HH:MM:SS)
                $startTime = $validatedData['start_time'];
                $endTime = $validatedData['end_time'];
                
                // Parse and format time properly for database time column.
                // Accept inputs like '16:00', '16:00:00', '4:00 PM', or full datetimes.
                $parseTime = function ($time, $fallback) {
                    if (empty($time)) {
                        return $fallback;
                    }

                    $formats = [
                        'H:i:s',
                        'H:i',
                        'g:i A',
                        'h:i A',
                        'Y-m-d H:i:s',
                        'Y-m-d g:i A',
                        'Y-m-d H:i',
                    ];

                    foreach ($formats as $fmt) {
                        try {
                            $dt = \Carbon\Carbon::createFromFormat($fmt, $time);
                            return $dt->format('H:i:s');
                        } catch (\Exception $e) {
                            // continue trying other formats
                        }
                    }

                    // Last resort: try generic parse
                    try {
                        return \Carbon\Carbon::parse($time)->format('H:i:s');
                    } catch (\Exception $e) {
                        Log::warning('Error parsing time value: ' . $e->getMessage(), ['time' => $time]);
                        return $fallback;
                    }
                };

                $startTime = $parseTime($startTime, '00:00:00');
                $endTime = $parseTime($endTime, '23:59:59');
                
                WorkPreference::create([
                    'applicant_id' => $applicant->applicant_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'days_available' => $request->input('days', []),
                    'platform' => $request->input('platforms', []),
                    'can_teach' => $request->input('can_teach', []),
                ]);

                // 6. Create Application
                $application = Application::create([
                    'applicant_id' => $applicant->applicant_id,
                    'attempt_count' => 0,
                    'status' => 'pending',
                    // Use correct column name: term_agreement (singular)
                    'term_agreement' => $termsAgreement ? 1 : 0,
                    'application_date_time' => now(),
                ]);

                return $application;
            });

            Log::info('Application created successfully', ['application_id' => $application->application_id]);
            
            // Create notification for new application submission
            try {
                $middleName = !empty($validatedData['middle_name']) ? $validatedData['middle_name'] . ' ' : '';
                $fullName = $validatedData['first_name'] . ' ' . $middleName . $validatedData['last_name'];
                
                $this->createNotification(
                    'info',
                    'New Application Submitted',
                    "A new application has been submitted by {$fullName} ({$validatedData['email']}). Please review the application in the hiring & onboarding section.",
                    'fas fa-user-plus',
                    'blue',
                    [
                        'application_id' => $application->application_id,
                        'applicant_name' => $fullName,
                        'applicant_email' => $validatedData['email'],
                        'submitted_at' => now()->toISOString()
                    ]
                );
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the application creation
                Log::warning('Notification creation failed: ' . $notificationError->getMessage());
            }
            
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Application creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $validatedData ?? []
            ]);
            
            // Transaction will automatically rollback if exception is thrown
            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit application. Please try again. If the problem persists, contact support.'])
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
        
        // Eager load all relationships to prevent N+1 queries
        $query = Application::with([
            'applicant',
            'applicant.qualification',
            'applicant.requirement',
            'applicant.referral',
            'applicant.workPreference'
        ]);
        
        // Exclude declined, not_recommended, and rejected statuses from new applicant list
        $query->whereNotIn('status', ['declined', 'not_recommended', 'rejected']);
        
        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) { // Allow search with just 1 character
                $query->whereHas('applicant', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('applicant.workPreference', function($q) use ($search) {
                    $q->where('start_time', 'like', "%{$search}%")
                      ->orWhere('end_time', 'like', "%{$search}%");
                })->orWhere('status', 'like', "%{$search}%");
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
                $query->whereHas('applicant.referral', function($q) use ($source) {
                    $q->where('source', 'like', "%{$source}%");
                });
            }
        }
        
        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'asc');
        
        // Handle different sort fields
        if ($sortField === 'first_name') {
            $query->join('applicants', 'application.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.first_name', $sortDirection)
                  ->select('application.*');
        } elseif ($sortField === 'interview_time') {
            $query->join('applicants', 'application.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.interview_time', $sortDirection)
                  ->select('application.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $applicants = $query->paginate(5)->withQueryString();
        
        // Get unique statuses and sources for filter dropdowns (exclude declined, not_recommended, rejected)
        $statuses = Application::whereNotIn('status', ['declined', 'not_recommended', 'rejected'])
            ->distinct()
            ->pluck('status')
            ->filter()
            ->values();
        $sources = Referral::distinct()->pluck('source')->filter()->values();
        
        return view('hiring_onboarding.index', compact('applicants', 'statuses', 'sources'));
    }

    /**
     * Display demo applications
     */
    public function viewDemo(Request $request)
    {
        // Work on the `screening` table (exposed through Demo proxy). Use applicant/account relations for searches.
        $query = Demo::with(['applicant', 'account']);

        // Exclude onboarding and hired applicants from the For Demo list
        $query->whereNotIn('phase', ['onboarding', 'hired']);
        
        // Exclude passed and failed results from the demo list
        $query->whereNotIn('results', ['passed', 'failed']);

        // Search across applicant and screening fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('applicant', function($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]) 
                           ->orWhere('contact_number', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('account', function($q3) use ($search) {
                        $q3->where('account_name', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(screening_date_time, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"]) ;
                });
            }
        }

        // Filter by phase/status - partial match
        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if (strlen($status) >= 1) {
                $query->where('phase', 'like', "%{$status}%");
            }
        }

        // Filter by assigned account - partial match (match account_name)
        if ($request->filled('account')) {
            $account = trim($request->input('account'));
            if (strlen($account) >= 1) {
                $query->whereHas('account', function($q) use ($account) {
                    $q->where('account_name', 'like', "%{$account}%");
                });
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'screening_date_time');
        $sortDirection = $request->get('direction', 'asc');
        
        // Handle different sort fields
        if ($sortField === 'first_name') {
            $query->join('applicants', 'screening.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.first_name', $sortDirection)
                  ->select('screening.*');
        } elseif ($sortField === 'assigned_account') {
            $query->leftJoin('accounts', 'screening.account_id', '=', 'accounts.account_id')
                  ->orderBy('accounts.account_name', $sortDirection)
                  ->select('screening.*');
        } elseif ($sortField === 'status') {
            $query->orderBy('phase', $sortDirection);
        } elseif ($sortField === 'created_at') {
            $query->orderBy('created_at', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $screenings = $query->paginate(5)->withQueryString();

        // Get unique phases and accounts for filter dropdowns (exclude passed, failed results and not_hired phase)
        $statuses = Demo::whereNotIn('phase', ['onboarding', 'hired', 'not_hired'])
            ->whereNotIn('results', ['passed', 'failed'])
            ->distinct()
            ->pluck('phase')
            ->filter()
            ->values();
        $accounts = \App\Models\Account::distinct()->pluck('account_name')->filter()->values();

        return view('hiring_onboarding.index', compact('screenings', 'statuses', 'accounts'));
    }

    
    /**
     * Display archived applications
     */
    public function viewArchive(Request $request)
    {
        $query = \App\Models\Archive::with('applicant');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('applicant', function($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('contact_number', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
                });
            }
        }

        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if (strlen($status) >= 1) {
                $query->where('status', 'like', "%{$status}%");
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'archive_date_time');
        $sortDirection = $request->get('direction', 'asc');
        
        // Handle different sort fields
        if ($sortField === 'first_name') {
            $query->join('applicants', 'archive.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.first_name', $sortDirection)
                  ->select('archive.*');
        } elseif ($sortField === 'archived_at') {
            $query->orderBy('archive_date_time', $sortDirection);
        } elseif ($sortField === 'interview_time') {
            $query->join('applicants', 'archive.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.interview_time', $sortDirection)
                  ->select('archive.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $archives = $query->paginate(5)->withQueryString();

        // Transform archives to match old view expectations (archivedApplicants)
        $transformed = $archives->getCollection()->map(function($a) {
            $payload = $a->payload ?? [];
            return (object) [
                'id' => $a->archive_id,
                'first_name' => $payload['first_name'] ?? ($a->applicant->first_name ?? null),
                'last_name' => $payload['last_name'] ?? ($a->applicant->last_name ?? null),
                'contact_number' => $payload['contact_number'] ?? ($a->applicant->contact_number ?? null),
                'email' => $payload['email'] ?? ($a->applicant->email ?? null),
                'notes' => $payload['notes'] ?? ($a->notes ?? null),
                'status' => $a->status ?? ($payload['status'] ?? null),
                'interview_time' => isset($payload['interview_time']) ? \Carbon\Carbon::parse($payload['interview_time']) : ($a->payload['interview_time'] ?? null),
                'archived_at' => $a->archive_date_time,
                'payload' => $payload,
            ];
        });

        $archives->setCollection($transformed);

        $statuses = \App\Models\Archive::distinct()->pluck('status')->filter()->values();

        // Keep variable name used by views
        $archivedApplicants = $archives;

        return view('hiring_onboarding.index', compact('archivedApplicants', 'statuses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        // Eager load all relationships for the view
        $application->load([
            'applicant',
            'applicant.qualification',
            'applicant.requirement',
            'applicant.referral',
            'applicant.workPreference'
        ]);
        
        return view('hiring_onboarding.applicant-details', compact('application'));
    }

    public function showUneditable($id)
    {
        // Check if we're coming from the onboarding tab
        if (request()->get('tab') === 'onboarding') {
            $demo = \App\Models\Onboarding::with([
                'applicant.qualification',
                'applicant.requirement',
                'applicant.referral',
                'applicant.workPreference',
                'account'
            ])->findOrFail($id);
        } else {
            $demo = Demo::with([
                'applicant.qualification',
                'applicant.requirement',
                'applicant.referral',
                'applicant.workPreference',
                'account'
            ])->findOrFail($id);
        }
        
        // Return the partial view with data
        return view('hiring_onboarding.applicant-details-unedited', compact('demo'));
    }

    /**
     * Display archived applicant details
     */
    public function showArchived(\App\Models\Archive $archive)
    {
        // Load applicant relationships for fallback data
        $archive->load([
            'applicant.qualification',
            'applicant.requirement',
            'applicant.referral',
            'applicant.workPreference'
        ]);

        // Transform archive record into expected view variable `$archivedApplication`
        $payload = $archive->payload ?? [];
        $applicant = $archive->applicant;

        // Helper function to format time
        $formatTime = function($time) {
            if (!$time) return null;
            if ($time instanceof \Carbon\Carbon) {
                return $time->format('h:i A');
            }
            return \Carbon\Carbon::parse($time)->format('h:i A');
        };

        $archivedApplication = (object) [
            'first_name' => $payload['first_name'] ?? ($applicant->first_name ?? null),
            'middle_name' => $payload['middle_name'] ?? ($applicant->middle_name ?? null),
            'last_name' => $payload['last_name'] ?? ($applicant->last_name ?? null),
            'birth_date' => isset($payload['birth_date']) ? \Carbon\Carbon::parse($payload['birth_date']) : ($applicant->birth_date ?? null),
            'address' => $payload['address'] ?? ($applicant->address ?? null),
            'contact_number' => $payload['contact_number'] ?? ($applicant->contact_number ?? null),
            'email' => $payload['email'] ?? ($applicant->email ?? null),
            'ms_teams' => $payload['ms_teams'] ?? ($applicant->ms_teams ?? null),
            'education' => $payload['education'] ?? ($applicant->qualification->education ?? null),
            'esl_experience' => $payload['esl_experience'] ?? ($applicant->qualification->esl_experience ?? null),
            'resume_link' => $payload['resume_link'] ?? ($applicant->requirement->resume_link ?? null),
            'intro_video' => $payload['intro_video'] ?? ($applicant->requirement->intro_video ?? null),
            'work_type' => $payload['work_type'] ?? ($applicant->requirement->work_type ?? null),
            'speedtest' => $payload['speedtest'] ?? ($applicant->requirement->speedtest ?? null),
            'main_device' => $payload['main_device'] ?? ($applicant->requirement->main_devices ?? null),
            'backup_device' => $payload['backup_device'] ?? ($applicant->requirement->backup_devices ?? null),
            'source' => $payload['source'] ?? ($applicant->referral->source ?? null),
            'referrer_name' => $payload['referrer_name'] ?? ($applicant->referral->referrer_name ?? null),
            'start_time' => $formatTime($payload['start_time'] ?? ($applicant->workPreference->start_time ?? null)),
            'end_time' => $formatTime($payload['end_time'] ?? ($applicant->workPreference->end_time ?? null)),
            'days' => $payload['days'] ?? ($applicant->workPreference->days_available ?? []),
            'platforms' => $payload['platforms'] ?? ($applicant->workPreference->platform ?? []),
            'can_teach' => $payload['can_teach'] ?? ($applicant->workPreference->can_teach ?? []),
            'interview_time' => isset($payload['interview_time']) ? \Carbon\Carbon::parse($payload['interview_time']) : ($applicant->interview_time ?? null),
            'status' => $archive->status ?? ($payload['status'] ?? null),
            'attempt_count' => $payload['attempt_count'] ?? null,
            'archived_at' => $archive->archive_date_time,
            'interviewer' => $payload['interviewer'] ?? ($archive->notes ?? null),
            'notes' => $payload['notes'] ?? ($archive->notes ?? null),
        ];

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

        // Update interviewer and notes in application table
        $application->interviewer = $interviewer;
        $application->notes = $notes;
        $application->save();

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
                    'application_id' => $application->application_id,
                    'applicant_name' => $application->first_name . ' ' . $application->last_name,
                        'status' => $specialStatus,
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
                        'application_id' => $application->application_id,
                        'applicant_name' => $application->first_name . ' ' . $application->last_name,
                            'status' => 'no_answer',
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
                        'application_id' => $application->application_id,
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
                $applicant = $application->applicant;
                $applicant->interview_time = $interviewTime;
                $applicant->save();
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
                    'application_id' => $application->application_id,
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
        
        // Send email notification to applicant
        $applicantEmail = $application->applicant->email ?? $application->email;
        if ($applicantEmail) {
            try {
                Mail::to($applicantEmail)->send(new \App\Mail\ApplicantPassedMail(
                    $application->first_name . ' ' . $application->last_name,
                    $applicantEmail,
                    'application', // phase they just passed
                    $nextStatus, // next phase (demo/screening/training)
                    $demoSchedule,
                    $interviewer,
                    $notes ?? 'Congratulations! You have passed the initial application review.'
                ));
                Log::info('New applicant pass email sent to: ' . $applicantEmail);
            } catch (\Exception $e) {
                Log::error('Failed to send new applicant pass email: ' . $e->getMessage());
            }
        }
        
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
        // Query from onboarding table instead of screening/demo table
        $query = \App\Models\Onboarding::with(['applicant', 'account']);
    
        // Search across all visible table fields
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if (strlen($search) >= 1) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('applicant', function($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                           ->orWhere('contact_number', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('account', function($q3) use ($search) {
                        $q3->where('account_name', 'like', "%{$search}%");
                    })
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(onboarding_date_time, '%Y-%m-%d %H:%i') LIKE ?", ["%{$search}%"]);
                });
            }
        }
    
        // Optional account filter - partial match
        if ($request->filled('account')) {
            $account = trim($request->input('account'));
            if (strlen($account) >= 1) {
                $query->whereHas('account', function($q) use ($account) {
                    $q->where('account_name', 'like', "%{$account}%");
                });
            }
        }
    
        // Sorting
        $sortField = $request->get('sort', 'onboarding_date_time');
        $sortDirection = $request->get('direction', 'asc');
        
        // Handle different sort fields
        if ($sortField === 'first_name') {
            $query->join('applicants', 'onboardings.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.first_name', $sortDirection)
                  ->select('onboardings.*');
        } elseif ($sortField === 'assigned_account') {
            $query->leftJoin('accounts', 'onboardings.account_id', '=', 'accounts.account_id')
                  ->orderBy('accounts.account_name', $sortDirection)
                  ->select('onboardings.*');
        } elseif ($sortField === 'interview_time') {
            $query->join('applicants', 'onboardings.applicant_id', '=', 'applicants.applicant_id')
                  ->orderBy('applicants.interview_time', $sortDirection)
                  ->select('onboardings.*');
        } elseif ($sortField === 'created_at') {
            $query->orderBy('created_at', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $onboardings = $query->paginate(5)->withQueryString();

        // Dropdown filter options - use account names
        $accounts = \App\Models\Account::distinct()->pluck('account_name')->filter()->values();
    
        // This view will be used for onboarding tab
        return view('hiring_onboarding.index', compact('onboardings', 'accounts'));
    }
    

    /**
     * Archive an application
     */
    private function archiveApplication(Application $application, string $finalStatus)
    {
        $applicant = $application->applicant;
        $qualification = $applicant->qualification;
        $requirement = $applicant->requirement;
        $referral = $applicant->referral;
        $workPreference = $applicant->workPreference;
        // Build payload with full snapshot so we can store in central `archive` table
        $payload = [
            'first_name' => $applicant->first_name,
            'middle_name' => $applicant->middle_name,
            'last_name' => $applicant->last_name,
            'birth_date' => $applicant->birth_date,
            'address' => $applicant->address,
            'contact_number' => $applicant->contact_number,
            'email' => $applicant->email,
            'ms_teams' => $applicant->ms_teams,
            'education' => $qualification->education ?? null,
            'esl_experience' => $qualification->esl_experience ?? null,
            'resume_link' => $requirement->resume_link ?? null,
            'intro_video' => $requirement->intro_video ?? null,
            'work_type' => $requirement->work_type ?? null,
            'speedtest' => $requirement->speedtest ?? null,
            'main_device' => $requirement->main_devices ?? null,
            'backup_device' => $requirement->backup_devices ?? null,
            'source' => $referral->source ?? null,
            'referrer_name' => $referral->referrer_name ?? null,
            'start_time' => $workPreference ? $workPreference->start_time : null,
            'end_time' => $workPreference ? $workPreference->end_time : null,
            'days' => $workPreference ? $workPreference->days_available : null,
            'platforms' => $workPreference ? $workPreference->platform : null,
            'can_teach' => $workPreference ? $workPreference->can_teach : null,
            'interview_time' => $applicant->interview_time,
            'notes' => $application->notes ?? null,
            'attempt_count' => $application->attempt_count,
        ];

        // Determine current supervisor id (try auth then session)
        $archiveBy = auth()->guard('supervisor')->check() 
            ? auth()->guard('supervisor')->user()->supervisor_id 
            : session('supervisor_id');

        // Create central archive record (category: application)
        \App\Models\Archive::create([
            'applicant_id' => $applicant->applicant_id,
            'archive_by' => $archiveBy,
            'notes' => $application->notes ?? '',
            'archive_date_time' => now(),
            'category' => 'application',
            'status' => $finalStatus,
            'payload' => $payload,
        ]);
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

        // Update interview time in applicant table
        $applicant = $application->applicant;
        $applicant->interview_time = $interviewTime;
        $applicant->save();

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
        $applicant = $application->applicant;
        $qualification = $applicant->qualification;
        $requirement = $applicant->requirement;
        $referral = $applicant->referral;
        $workPreference = $applicant->workPreference;
        
        $demoData = [
            'first_name' => $applicant->first_name,
            'last_name' => $applicant->last_name,
            'birth_date' => $applicant->birth_date,
            'address' => $applicant->address,
            'contact_number' => $applicant->contact_number,
            'email' => $applicant->email,
            'ms_teams' => $applicant->ms_teams,
            'education' => $qualification->education ?? null,
            'esl_experience' => $qualification->esl_experience ?? null,
            'resume_link' => $requirement->resume_link ?? null,
            'intro_video' => $requirement->intro_video ?? null,
            'work_type' => $requirement->work_type ?? null,
            'speedtest' => $requirement->speedtest ?? null,
            'main_device' => $requirement->main_devices ?? null,
            'backup_device' => $requirement->backup_devices ?? null,
            'source' => $referral->source ?? null,
            'referrer_name' => $referral->referrer_name ?? null,
            'start_time' => $workPreference ? $workPreference->start_time : ($startTime ?? null),
            'end_time' => $workPreference ? $workPreference->end_time : ($endTime ?? null),
            'days' => $workPreference ? $workPreference->days_available : null,
            'platforms' => $workPreference ? $workPreference->platform : null,
            'can_teach' => $workPreference ? $workPreference->can_teach : null,
            'interview_time' => $applicant->interview_time,
            'status' => $nextStatus,
            'assigned_account' => $assignedAccount,
            'interviewer' => $interviewer,
            'notes' => $notes,
            'demo_schedule' => $demoSchedule,
            'moved_to_demo_at' => now(),
        ];
        
        // Create a screening record instead of inserting into the old `demos` table.
        // Map assigned account name to account_id if possible
        $account = Account::where('account_name', $assignedAccount)->first();
        
        if (!$account) {
            throw new \Exception("Account '{$assignedAccount}' not found. Please ensure all accounts (gls, talk915, babilala, tutlo) are seeded in the database.");
        }

        // Get authenticated supervisor ID
        $supervisorId = auth()->guard('supervisor')->check() 
            ? auth()->guard('supervisor')->user()->supervisor_id 
            : session('supervisor_id');

        Screening::create([
            'applicant_id' => $applicant->applicant_id,
            'supervisor_id' => $supervisorId,
            'account_id' => $account->account_id,
            'phase' => $nextStatus,
            'results' => 'pending',
            'notes' => trim(($notes ? $notes . ' | ' : '') . ($interviewer ? "interviewer: {$interviewer}" : '')),
            'screening_date_time' => $demoSchedule ? \Carbon\Carbon::parse($demoSchedule) : now(),
        ]);
    }

    /**
     * Get demo data for editing
     */
    public function getDemoEditData($id)
    {
        try {
            // Eager load the account and applicant relationships
            $demo = Demo::with(['account', 'applicant'])->findOrFail($id);
            
            // Get assignment history for this applicant (all accounts they've been assigned to)
            $assignmentHistory = $this->getAssignmentHistory($demo);
            
            // Try to extract interviewer from notes if present (we stored it there when creating screening)
            $interviewer = null;
            if (!empty($demo->notes) && preg_match('/interviewer:\s*([^|]+)/i', $demo->notes, $m)) {
                $interviewer = trim($m[1]);
            }

            return response()->json([
                'interviewer' => $interviewer,
                'email' => $demo->email,
                'start_time' => null,
                'end_time' => null,
                'assigned_account' => $demo->assigned_account,
                'hiring_status' => $demo->status,
                'schedule' => $demo->demo_schedule,
                'notes' => $demo->notes,
                'assignment_history' => $assignmentHistory,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDemoEditData: ' . $e->getMessage());
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
        
        // Get all screenings for this applicant (by applicant_id) and collect account names
        $demoAccountIds = Demo::where('applicant_id', $demo->applicant_id)
            ->whereNotNull('account_id')
            ->pluck('account_id')
            ->toArray();

        $demoAccounts = Account::whereIn('account_id', $demoAccountIds)
            ->pluck('account_name')
            ->toArray();

        // Get all archived applications for this applicant (by email) from central archive payload
        $archivedAccounts = \App\Models\Archive::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.email')) = ?", [$demo->email])
            ->whereRaw("JSON_EXTRACT(payload, '$.assigned_account') IS NOT NULL")
            ->pluck(\Illuminate\Support\Facades\DB::raw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.assigned_account'))"))
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

        // Update screening record: map assigned_account to account_id and status -> phase
        $account = Account::where('account_name', $request->input('assigned_account'))->first();

        $demo->update([
            'account_id' => $account?->account_id,
            'phase' => $request->input('hiring_status'),
            'screening_date_time' => $request->input('schedule'),
            'notes' => trim(($request->input('notes') ?? '') . ' | interviewer: ' . $request->input('interviewer')),
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
            'phase' => $request->input('next_status'),
            'screening_date_time' => $request->input('next_schedule'),
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
            // Move to onboarding phase and mark result
            $demo->update([
                'phase' => 'onboarding',
                'results' => 'hired',
                'notes' => trim(($demo->notes ?? '') . ' | finalized_at: ' . now()->toDateTimeString()),
            ]);

            return redirect()->route('hiring_onboarding.index', ['tab' => 'demo'])
                ->with('success', 'Applicant has been successfully hired and moved to onboarding!');
        } else {
            // Mark as not hired
            $demo->update([
                'phase' => 'not_hired',
                'results' => 'failed',
                'notes' => trim(($demo->notes ?? '') . ' | finalized_at: ' . now()->toDateTimeString()),
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
