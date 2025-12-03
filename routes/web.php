<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ============================================================================
// CONTROLLERS
// ============================================================================
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentInformationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleExportController;
use App\Http\Controllers\SupervisorProfileController;
use App\Http\Controllers\TutorAssignmentController;
use App\Http\Controllers\EmployeeManagementController;
use App\Http\Controllers\TutorAvailabilityController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SimplePasswordResetController;

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Landing Page
Route::get('/', fn() => view('landing.index'))->name('landing');

// ============================================================================
// APPLICATION FORM ROUTES (Public)
// ============================================================================
Route::prefix('application-form')->name('application.form.')->group(function () {
    Route::get('/', fn() => view('application_form.application'))->name('index');
    Route::get('/cancel', fn() => view('application_form.cancel'))->name('cancel');
    Route::get('/success', fn() => view('application_form.submit'))->name('success');
    Route::post('/submit', [ApplicationFormController::class, 'store'])->name('submit');
});

// ============================================================================
// PUBLIC API ROUTES
// ============================================================================
Route::post('/api/get-security-question', [SimplePasswordResetController::class, 'getSecurityQuestion']);

// ============================================================================
// AUTHENTICATED ROUTES (Supervisor & Web Users)
// ============================================================================
Route::middleware(['auth:supervisor,web', 'prevent.back'])->group(function () {
    
    // ------------------------------------------------------------------------
    // DASHBOARD
    // ------------------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ------------------------------------------------------------------------
    // SCHEDULING
    // ------------------------------------------------------------------------
    Route::prefix('scheduling')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
    });
    Route::get('/class-scheduling', fn() => view('schedules.class-scheduling'))->name('class-scheduling');
    
    // ------------------------------------------------------------------------
    // EMPLOYEE MANAGEMENT
    // ------------------------------------------------------------------------
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeManagementController::class, 'index'])->name('index');
        Route::get('/tutor/{tutor}', [EmployeeManagementController::class, 'viewTutor'])->name('tutor.view');
        Route::get('/supervisor/{supervisor}', [EmployeeManagementController::class, 'viewSupervisor'])->name('supervisor.view');
        Route::post('/archive', [EmployeeManagementController::class, 'archive'])->name('archive');
        Route::get('/archived', [EmployeeManagementController::class, 'getArchivedEmployees'])->name('archived');
    });
    
    // ------------------------------------------------------------------------
    // SUPERVISOR PROFILE
    // ------------------------------------------------------------------------
    Route::prefix('supervisor/profile')->name('supervisor.')->group(function () {
        Route::get('/', [SupervisorProfileController::class, 'index'])->name('profile');
        Route::post('/security-questions', [SupervisorProfileController::class, 'updateSecurityQuestions'])->name('security-questions.update');
        Route::post('/role', [SupervisorProfileController::class, 'updateRole'])->name('role.update');
        Route::post('/personal-info', [SupervisorProfileController::class, 'updatePersonalInfo'])->name('personal-info.update');
        Route::post('/password', [SupervisorProfileController::class, 'updatePassword'])->name('password.update');
    });
    
    // ------------------------------------------------------------------------
    // IMPORT
    // ------------------------------------------------------------------------
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    
    // ------------------------------------------------------------------------
    // HIRING & ONBOARDING
    // ------------------------------------------------------------------------
    Route::prefix('hiring-onboarding')->name('hiring_onboarding.')->group(function () {
        // Main index
        Route::get('/', [ApplicationFormController::class, 'viewTable'])->name('index');
        Route::patch('/{id}', [ApplicationFormController::class, 'update'])->name('update');
        
        // Applicant routes
        Route::prefix('applicant')->name('applicant.')->group(function () {
            // View editable applicant (from applications table)
            Route::get('/{application}', [ApplicationFormController::class, 'show'])
                ->name('show')
                ->where('application', '[0-9]+');
            
            // View uneditable applicant (from screening/demo table)
            Route::get('/{demo}/uneditable', [ApplicationFormController::class, 'showUneditable'])
                ->name('showUneditable')
                ->where('demo', '[0-9]+');
            
            // Pass/Fail actions
            Route::patch('/{application}/pass', [ApplicationFormController::class, 'handlePass'])->name('pass');
            Route::patch('/{application}/fail', [ApplicationFormController::class, 'handleFail'])->name('fail');
            Route::patch('/{application}/archive-reschedule', [ApplicationFormController::class, 'archiveReschedule'])->name('archive_reschedule');
        });
        
        // Archive routes
        Route::get('/archived/{archive}', [ApplicationFormController::class, 'showArchived'])->name('archived.show');
        Route::get('/archive', [ApplicationFormController::class, 'viewArchive'])->name('archive');
        
        // Onboarding
        Route::get('/onboarding', [ApplicationFormController::class, 'viewOnboarding'])->name('onboarding');
    });
    
    // ------------------------------------------------------------------------
    // DEMO MANAGEMENT
    // ------------------------------------------------------------------------
    Route::prefix('demo')->name('demo.')->group(function () {
        Route::get('/{id}/edit-data', [ApplicationFormController::class, 'getDemoEditData'])->name('edit.data');
        Route::get('/{id}/generate-username', [ApplicationController::class, 'generateUsername'])->name('generate.username');
        Route::patch('/{demo}', [ApplicationFormController::class, 'updateDemo'])->name('update');
        Route::patch('/{demo}/status', [ApplicationController::class, 'updateDemoStatus'])->name('update.status');
        Route::patch('/{id}/fail', [ApplicationController::class, 'handleFail'])->name('fail');
        Route::post('/{demo}/finalize', [ApplicationFormController::class, 'finalizeDemo'])->name('finalize');
    });
    
    Route::prefix('demos')->name('demo.')->group(function () {
        Route::post('/{id}/register-tutor', [ApplicationController::class, 'registerTutor'])->name('register.tutor');
        Route::post('/{id}/generate-unique-username', [ApplicationController::class, 'generateUniqueUsername'])->name('generate.username');
        Route::post('/{id}/generate-unique-email', [ApplicationController::class, 'generateUniqueEmail'])->name('generate.email');
        Route::post('/generate-tutor-id', [ApplicationController::class, 'generateTutorId'])->name('generate.tutor.id');
    });
    
    // Legacy route compatibility
    Route::post('/demo/{demo}/finalize', [ApplicationFormController::class, 'finalizeDemo'])->name('applicants.finalize');
    
    // ------------------------------------------------------------------------
    // NOTIFICATIONS
    // ------------------------------------------------------------------------
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'viewAll'])->name('index');
        Route::get('/api', [NotificationController::class, 'index'])->name('api');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });
    
    // ------------------------------------------------------------------------
    // PAYMENT INFORMATION
    // ------------------------------------------------------------------------
    Route::prefix('payment-information')->name('payment-information.')->group(function () {
        Route::get('/', [PaymentInformationController::class, 'index'])->name('index');
        Route::post('/', [PaymentInformationController::class, 'store'])->name('store');
        Route::get('/{employeeType}/{employeeId}', [PaymentInformationController::class, 'show'])->name('show');
    });
    Route::get('/payment-information-all', [PaymentInformationController::class, 'getAll'])->name('payment-information.all');
});


// ============================================================================
// TUTOR ROUTES
// ============================================================================
Route::middleware(['auth:tutor', 'prevent.back'])->group(function () {
    
    // ------------------------------------------------------------------------
    // TUTOR PORTAL
    // ------------------------------------------------------------------------
    Route::get('/tutor_portal', function () {
        $tutor = Auth::guard('tutor')->user();
        $tutor->load([
            'applicant.qualification', 
            'applicant.requirement', 
            'applicant.workPreference',
            // TODO: Uncomment when employee_payment_information table exists
            // 'paymentInformation', 
            // TODO: Uncomment when security_questions relationship is fixed
            // 'securityQuestions'
        ]);
        return view('tutor.tutor_portal', compact('tutor'));
    })->name('tutor.portal');
    
    // ------------------------------------------------------------------------
    // TUTOR AVAILABILITY
    // ------------------------------------------------------------------------
    Route::prefix('tutor/availability')->name('tutor.availability.')->group(function () {
        Route::get('/', [TutorAvailabilityController::class, 'getAvailability'])->name('get');
        Route::post('/update', [TutorAvailabilityController::class, 'updateAvailability'])->name('update');
        Route::post('/update-multiple', [TutorAvailabilityController::class, 'updateMultipleAccounts'])->name('update.multiple');
        Route::get('/time-slots', [TutorAvailabilityController::class, 'getTimeSlots'])->name('time.slots');
    });
    
    // ------------------------------------------------------------------------
    // TUTOR PAYMENTS
    // ------------------------------------------------------------------------
    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::post('/setup-payment', [TutorAvailabilityController::class, 'setupPayment'])->name('setup-payment');
        Route::put('/payment-method/{paymentId}', [TutorAvailabilityController::class, 'updatePaymentMethod'])->name('update-payment-method');
        Route::delete('/payment-method/{paymentId}', [TutorAvailabilityController::class, 'deletePaymentMethod'])->name('delete-payment-method');
    });
    
    // ------------------------------------------------------------------------
    // TUTOR PROFILE
    // ------------------------------------------------------------------------
    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::post('/update-personal-info', [TutorAvailabilityController::class, 'updatePersonalInfo'])->name('update-personal-info');
        Route::post('/change-password', [TutorAvailabilityController::class, 'changePassword'])->name('change-password');
        Route::post('/update-security-questions', [TutorAvailabilityController::class, 'updateSecurityQuestions'])->name('update-security-questions');
    });
});

// ============================================================================
// LOGOUT ROUTES
// ============================================================================
Route::post('/supervisor-logout', [AuthenticatedSessionController::class, 'destroy'])->name('supervisor.logout');
Route::post('/tutor-logout', [AuthenticatedSessionController::class, 'destroy'])->name('tutor.logout');

// ============================================================================
// AUTHENTICATED API ROUTES (Supervisor & Web Users)
// ============================================================================
Route::middleware(['auth:supervisor,web', 'prevent.back'])->prefix('api')->name('api.')->group(function () {
    
    // ------------------------------------------------------------------------
    // ASSIGNMENT API
    // ------------------------------------------------------------------------
    Route::get('/available-tutors', [ScheduleController::class, 'getAvailableTutors'])->name('available-tutors');
    Route::get('/class-tutors/{classId}', [TutorAssignmentController::class, 'getClassTutors'])->name('class-tutors');
    Route::post('/save-tutor-assignments', [TutorAssignmentController::class, 'saveAssignments'])->name('save-tutor-assignments');
    Route::post('/check-tutor-time-conflict', [ScheduleController::class, 'checkTutorTimeConflict'])->name('check-tutor-time-conflict');
    
    // ------------------------------------------------------------------------
    // SEARCH API
    // ------------------------------------------------------------------------
    Route::get('/search-schedules', [ScheduleController::class, 'searchSchedules'])->name('search-schedules');
    Route::get('/search-tutors', [ScheduleController::class, 'searchTutors'])->name('search-tutors');
    
    // ------------------------------------------------------------------------
    // DASHBOARD API
    // ------------------------------------------------------------------------
    Route::get('/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard-data');
    Route::get('/dashboard-weekly-trends', [DashboardController::class, 'getWeeklyTrendsData'])->name('dashboard-weekly-trends');
    
    // ------------------------------------------------------------------------
    // DEBUG/UTILITY API
    // ------------------------------------------------------------------------
    Route::get('/debug-tutors', function () {
        $tutors = \App\Models\Tutor::with(['accounts' => function ($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        }])
            ->whereHas('accounts', function ($query) {
                $query->where('account_name', 'GLS')->where('status', 'active');
            })
            ->where('status', 'active')
            ->take(3)
            ->get();

        $result = $tutors->map(function ($tutor) {
            return [
                'id' => $tutor->tutorID,
                'username' => $tutor->username,
                'full_name' => $tutor->full_name,
                'status' => $tutor->status,
                'accounts' => $tutor->accounts->map(function ($account) {
                    return [
                        'account_name' => $account->account_name,
                        'status' => $account->status,
                        'available_times' => $account->available_times
                    ];
                })
            ];
        });

        return response()->json($result);
    })->name('debug-tutors');
});

// Assignment status check (separate route with optional parameter)
Route::middleware(['auth:supervisor,web', 'prevent.back'])->group(function () {
    Route::get('/check-assignments/{date?}', function ($date = null) {
        $date = $date ?: \App\Models\DailyData::select('date')->distinct()->orderBy('date')->first()?->date;

        if (!$date) {
            return response()->json(['error' => 'No dates available']);
        }

        $classes = \App\Models\DailyData::where('date', $date)->with('tutorAssignments.tutor')->get();
        $status = [];

        foreach ($classes as $class) {
            $assigned = $class->tutorAssignments->count();
            $needed = $class->number_required;
            $tutors = $class->tutorAssignments->pluck('tutor.username')->toArray();

            $status[] = [
                'id' => $class->id,
                'school' => $class->school,
                'class' => $class->class,
                'time' => $class->time_jst,
                'needed' => $needed,
                'assigned' => $assigned,
                'status' => $assigned >= $needed ? 'full' : ($assigned > 0 ? 'partial' : 'empty'),
                'tutors' => $tutors
            ];
        }

        return response()->json([
            'date' => $date,
            'classes' => $status,
            'summary' => [
                'total_classes' => count($status),
                'fully_assigned' => count(array_filter($status, fn($s) => $s['status'] === 'full')),
                'partially_assigned' => count(array_filter($status, fn($s) => $s['status'] === 'partial')),
                'empty' => count(array_filter($status, fn($s) => $s['status'] === 'empty'))
            ]
        ]);
    })->name('check-assignments');
});

// ============================================================================
// SUPERVISOR-ONLY ROUTES
// ============================================================================
Route::middleware(['auth:supervisor', 'prevent.back'])->group(function () {
    
    // ------------------------------------------------------------------------
    // SCHEDULE AUTO-ASSIGNMENT
    // ------------------------------------------------------------------------
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::post('/auto-assign', [TutorAssignmentController::class, 'autoAssign'])->name('auto-assign');
        Route::post('/auto-assign/{date}', [TutorAssignmentController::class, 'autoAssignForDate'])->name('auto-assign.date');
        Route::post('/auto-assign/{date}/{day}', [TutorAssignmentController::class, 'autoAssignForSpecific'])->name('auto-assign.specific');
        Route::post('/auto-assign-class/{class}', [TutorAssignmentController::class, 'autoAssignForClass'])->name('auto-assign.class');
        Route::delete('/remove-assignment/{assignment}', [TutorAssignmentController::class, 'removeAssignment'])->name('remove-assignment');
    });
    
    // ------------------------------------------------------------------------
    // SCHEDULE MANAGEMENT
    // ------------------------------------------------------------------------
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::post('/cancel-class/{classId}', [ScheduleController::class, 'cancelClass'])->name('cancel-class');
        Route::post('/save-schedule', [ScheduleController::class, 'saveSchedule'])->name('save-schedule');
        Route::get('/history', [ScheduleController::class, 'showScheduleHistory'])->name('history');
    });
    
    // ------------------------------------------------------------------------
    // SCHEDULE EXPORTS
    // ------------------------------------------------------------------------
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/export-history', [ScheduleExportController::class, 'exportHistory'])->name('export-history');
        Route::get('/export-tentative', [ScheduleExportController::class, 'exportTentative'])->name('export-tentative');
        Route::get('/export-final', [ScheduleExportController::class, 'exportFinal'])->name('export-final');
        Route::post('/export-selected', [ScheduleExportController::class, 'exportSelected'])->name('export-selected');
    });
    
    // ------------------------------------------------------------------------
    // TUTOR MANAGEMENT
    // ------------------------------------------------------------------------
    Route::post('/tutors/{tutor}/toggle-status', [ScheduleController::class, 'toggleTutorStatus'])->name('tutors.toggle-status');
});

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================
require __DIR__ . '/auth.php';
