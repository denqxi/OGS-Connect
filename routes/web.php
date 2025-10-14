<?php

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TutorAssignmentController;
use App\Http\Controllers\ScheduleExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentInformationController;
use App\Http\Controllers\SupervisorProfileController;

use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\ApplicationController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('landing');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// API route for getting security questions (moved from api.php to avoid CSRF issues)
Route::post('/api/get-security-question', [\App\Http\Controllers\Auth\SimplePasswordResetController::class, 'getSecurityQuestion']);


//APPLICATION FORM ROUTES START

Route::get('/application-form', function () {
    return view('application_form.application');
})->name('application.form');

Route::get('/application-form/cancel', function () {
    return view('application_form.cancel'); // cancel.blade.php
})->name('application.form.cancel');

Route::get('/application-form/success', function () {
    return view('application_form.submit'); // submit.blade.php
})->name('application.form.success');

Route::post('/application-form/submit', [ApplicationFormController::class, 'store'])->name('application.form.submit');

//APPLICATION FORM ROUTES END

// Protected routes - require authentication
Route::middleware(['auth:supervisor,web', 'prevent.back'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scheduling', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/class-scheduling', function () {
        return view('schedules.class-scheduling');
    })->name('class-scheduling');
    Route::get('/employees', [\App\Http\Controllers\EmployeeManagementController::class, 'index'])->name('employees.index');
    Route::get('/employees/tutor/{tutor}', [\App\Http\Controllers\EmployeeManagementController::class, 'viewTutor'])->name('employees.tutor.view');
    Route::get('/employees/supervisor/{supervisor}', [\App\Http\Controllers\EmployeeManagementController::class, 'viewSupervisor'])->name('employees.supervisor.view');
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    Route::get('/supervisor/profile', [SupervisorProfileController::class, 'index'])->name('supervisor.profile');
    Route::post('/supervisor/profile/security-questions', [SupervisorProfileController::class, 'updateSecurityQuestions'])->name('supervisor.security-questions.update');
    Route::post('/supervisor/profile/role', [SupervisorProfileController::class, 'updateRole'])->name('supervisor.role.update');
    Route::post('/supervisor/profile/personal-info', [SupervisorProfileController::class, 'updatePersonalInfo'])->name('supervisor.personal-info.update');
    Route::post('/supervisor/profile/password', [SupervisorProfileController::class, 'updatePassword'])->name('supervisor.password.update');


    //------------------------------
    // For Hiring & Onboarding - New Applicants Information to Table
    Route::get('/hiring-onboarding', [ApplicationFormController::class, 'viewTable'])->name('hiring_onboarding.index');
    Route::patch('/hiring-onboarding/{id}', [ApplicationFormController::class, 'update'])->name('hiring-onboarding.update');

    // View Details of each Applicant
    Route::get('/hiring-onboarding/applicant/{application}', [ApplicationFormController::class, 'show'])
        ->name('hiring_onboarding.applicant.show')
        ->where('application', '[0-9]+');


    // View Details of each Applicant - Uneditable
    Route::get('/hiring-onboarding/applicant/{demo}/uneditable', [ApplicationFormController::class, 'showUneditable'])
        ->name('hiring_onboarding.applicant.showUneditable');

    // View Details of Archived Applicant
    Route::get('/hiring-onboarding/archived/{archivedApplication}', [ApplicationFormController::class, 'showArchived'])
        ->name('hiring_onboarding.archived.show');

    // Handle fail modal submission
    Route::patch('/hiring-onboarding/applicant/{application}/fail', [ApplicationFormController::class, 'handleFail'])->name('hiring_onboarding.applicant.fail');

    // Handle pass modal submission
    Route::patch('/hiring-onboarding/applicant/{application}/pass', [ApplicationFormController::class, 'handlePass'])->name('hiring_onboarding.applicant.pass');

    Route::post('/demos/{id}/register-tutor', [ApplicationController::class, 'registerTutor'])->name('demo.register.tutor');
    Route::get('/demo/{id}/generate-username', [ApplicationController::class, 'generateUsername'])->name('demo.generate.username');
    Route::patch('/demo/{id}/fail', [ApplicationController::class, 'handleFail'])->name('demo.fail');
    // Demo edit routes
    Route::get('/demo/{id}/edit-data', [ApplicationFormController::class, 'getDemoEditData'])->name('demo.edit.data');
    Route::patch('/demo/{demo}', [ApplicationFormController::class, 'updateDemo'])->name('demo.update');
    Route::patch('/demo/{demo}/status', [ApplicationController::class, 'updateDemoStatus'])->name('demo.update.status');
    Route::post('/demo/{demo}/finalize', [ApplicationFormController::class, 'finalizeDemo'])->name('applicants.finalize');

    // Onboarding Applicants
    Route::get('/hiring-onboarding/onboarding', [ApplicationFormController::class, 'viewOnboarding'])
        ->name('hiring_onboarding.onboarding');

    // Archive applications
    Route::get('/hiring-onboarding/archive', [ApplicationFormController::class, 'viewArchive'])->name('hiring_onboarding.archive');

    // Archive re-scheduled application
    Route::patch('/hiring-onboarding/applicant/{application}/archive-reschedule', [ApplicationFormController::class, 'archiveReschedule'])->name('hiring_onboarding.applicant.archive_reschedule');

    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'viewAll'])->name('notifications.index');
    Route::get('/notifications/api', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.api');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications', [App\Http\Controllers\NotificationController::class, 'store'])->name('notifications.store');

    //------------------------------



    // Payment Information Routes
    Route::get('/payment-information', [PaymentInformationController::class, 'index'])->name('payment-information.index');
    Route::post('/payment-information', [PaymentInformationController::class, 'store'])->name('payment-information.store');
    Route::get('/payment-information/{employeeType}/{employeeId}', [PaymentInformationController::class, 'show'])->name('payment-information.show');
    Route::get('/payment-information-all', [PaymentInformationController::class, 'getAll'])->name('payment-information.all');
});


Route::middleware(['auth:tutor', 'prevent.back'])->group(function () {
    Route::get('/tutor_portal', function () {
        $tutor = Auth::guard('tutor')->user();
        $tutor->load(['tutorDetails', 'paymentInformation', 'securityQuestions']); // Load the tutorDetails, paymentInformation, and securityQuestions relationships
        return view('tutor.tutor_portal', compact('tutor'));
    })->name('tutor.portal');


    // Tutor availability management routes
    Route::prefix('tutor/availability')->name('tutor.availability.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TutorAvailabilityController::class, 'getAvailability'])->name('get');
        Route::post('/update', [\App\Http\Controllers\TutorAvailabilityController::class, 'updateAvailability'])->name('update');
        Route::post('/update-multiple', [\App\Http\Controllers\TutorAvailabilityController::class, 'updateMultipleAccounts'])->name('update.multiple');
        Route::get('/time-slots', [\App\Http\Controllers\TutorAvailabilityController::class, 'getTimeSlots'])->name('time.slots');
    });

    // Tutor payment setup route
    Route::post('/tutor/setup-payment', [\App\Http\Controllers\TutorAvailabilityController::class, 'setupPayment'])->name('tutor.setup-payment');

    // Tutor payment method management routes
    Route::put('/tutor/payment-method/{paymentId}', [\App\Http\Controllers\TutorAvailabilityController::class, 'updatePaymentMethod'])->name('tutor.update-payment-method');
    Route::delete('/tutor/payment-method/{paymentId}', [\App\Http\Controllers\TutorAvailabilityController::class, 'deletePaymentMethod'])->name('tutor.delete-payment-method');

    // Tutor personal information and security routes
    Route::post('/tutor/update-personal-info', [\App\Http\Controllers\TutorAvailabilityController::class, 'updatePersonalInfo'])->name('tutor.update-personal-info');
    Route::post('/tutor/change-password', [\App\Http\Controllers\TutorAvailabilityController::class, 'changePassword'])->name('tutor.change-password');
    Route::post('/tutor/update-security-questions', [\App\Http\Controllers\TutorAvailabilityController::class, 'updateSecurityQuestions'])->name('tutor.update-security-questions');
});

// Custom logout route for supervisors (doesn't require auth middleware)
Route::post('/supervisor-logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('supervisor.logout');

// Custom logout route for tutors (doesn't require auth middleware)
Route::post('/tutor-logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('tutor.logout');

// Protected API routes - require authentication
Route::middleware(['auth:supervisor,web', 'prevent.back'])->group(function () {
    // Check assignment status route
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
            $tutors = $class->tutorAssignments->pluck('tutor.tusername')->toArray();

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
    });

    // API route to get available tutors
    Route::get('/api/available-tutors', [ScheduleController::class, 'getAvailableTutors'])->name('api.available-tutors');

    // Debug route to check tutor data
    Route::get('/api/debug-tutors', function () {
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
                'username' => $tutor->tusername,
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
    });

    // API route to get class tutors (main and backup)
    Route::get('/api/class-tutors/{classId}', [TutorAssignmentController::class, 'getClassTutors'])->name('api.class-tutors');

    // API route to save tutor assignments
    Route::post('/api/save-tutor-assignments', [TutorAssignmentController::class, 'saveAssignments'])->name('api.save-tutor-assignments');

    // API route for real-time search
    Route::get('/api/search-schedules', [ScheduleController::class, 'searchSchedules'])->name('api.search-schedules');
    Route::get('/api/search-tutors', [ScheduleController::class, 'searchTutors'])->name('api.search-tutors');

    // API route to check tutor time conflicts
    Route::post('/api/check-tutor-time-conflict', [ScheduleController::class, 'checkTutorTimeConflict'])->name('api.check-tutor-time-conflict');

    // Dashboard API routes
    Route::get('/api/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('api.dashboard-data');
    Route::get('/api/dashboard-weekly-trends', [DashboardController::class, 'getWeeklyTrendsData'])->name('api.dashboard-weekly-trends');
});

// Supervisor-only routes - require supervisor authentication
Route::middleware(['auth:supervisor', 'prevent.back'])->group(function () {
    // Auto-assign routes
    Route::post('/schedules/auto-assign', [TutorAssignmentController::class, 'autoAssign'])->name('schedules.auto-assign');
    Route::post('/schedules/auto-assign/{date}', [TutorAssignmentController::class, 'autoAssignForDate'])->name('schedules.auto-assign.date');
    Route::post('/schedules/auto-assign/{date}/{day}', [TutorAssignmentController::class, 'autoAssignForSpecific'])->name('schedules.auto-assign.specific');
    Route::post('/schedules/auto-assign-class/{class}', [TutorAssignmentController::class, 'autoAssignForClass'])->name('schedules.auto-assign.class');
    Route::delete('/schedules/remove-assignment/{assignment}', [TutorAssignmentController::class, 'removeAssignment'])->name('schedules.remove-assignment');

    // Class cancellation routes
    Route::post('/schedules/cancel-class/{classId}', [ScheduleController::class, 'cancelClass'])->name('schedules.cancel-class');

    // Schedule saving routes
    Route::post('/schedules/save-schedule', [ScheduleController::class, 'saveSchedule'])->name('schedules.save-schedule');

    // Schedule history routes
    Route::get('/schedules/history', [ScheduleController::class, 'showScheduleHistory'])->name('schedules.history');
    Route::get('/schedules/export-history', [ScheduleExportController::class, 'exportHistory'])->name('schedules.export-history');

    // Export routes
    Route::get('/schedules/export-tentative', [ScheduleExportController::class, 'exportTentative'])->name('schedules.export-tentative');
    Route::get('/schedules/export-final', [ScheduleExportController::class, 'exportFinal'])->name('schedules.export-final');
    Route::post('/schedules/export-selected', [ScheduleExportController::class, 'exportSelected'])->name('schedules.export-selected');

    // Tutor management routes
    Route::post('/tutors/{tutor}/toggle-status', [ScheduleController::class, 'toggleTutorStatus'])->name('tutors.toggle-status');
});


require __DIR__ . '/auth.php';
