<?php

use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TutorAssignmentController;
use App\Http\Controllers\ScheduleExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentInformationController;
use App\Http\Controllers\SupervisorProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('landing');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// APPLICATION FORM ROUTES
Route::get('/application-form', function () {
    return view('application_form.application'); // main application form
})->name('application.form');

Route::get('/application-form/cancel', function () {
    return view('application_form.cancel'); // cancel.blade.php
})->name('application.form.cancel');

Route::get('/application-form/submit', function () {
    return view('application_form.submit'); // submit.blade.php
})->name('application.form.submit');

//APPLICATION FORM ROUTES END

// Protected routes - require authentication
Route::middleware(['auth:supervisor,web', 'prevent.back'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scheduling', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/class-scheduling', function () {
        return view('schedules.class-scheduling');
    })->name('class-scheduling');
    Route::get('/employees', [\App\Http\Controllers\EmployeeManagementController::class, 'index'])->name('employees.index');
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    Route::get('/supervisor/profile', [SupervisorProfileController::class, 'index'])->name('supervisor.profile');
    Route::post('/supervisor/profile/security-questions', [SupervisorProfileController::class, 'updateSecurityQuestions'])->name('supervisor.security-questions.update');
    Route::post('/supervisor/profile/role', [SupervisorProfileController::class, 'updateRole'])->name('supervisor.role.update');
    Route::post('/supervisor/profile/personal-info', [SupervisorProfileController::class, 'updatePersonalInfo'])->name('supervisor.personal-info.update');
    Route::post('/supervisor/profile/password', [SupervisorProfileController::class, 'updatePassword'])->name('supervisor.password.update');
    
    // Payment Information Routes
    Route::get('/payment-information', [PaymentInformationController::class, 'index'])->name('payment-information.index');
    Route::post('/payment-information', [PaymentInformationController::class, 'store'])->name('payment-information.store');
    Route::get('/payment-information/{employeeType}/{employeeId}', [PaymentInformationController::class, 'show'])->name('payment-information.show');
    Route::get('/payment-information-all', [PaymentInformationController::class, 'getAll'])->name('payment-information.all');
});

// Custom logout route for supervisors (doesn't require auth middleware)
Route::post('/supervisor-logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('supervisor.logout');

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
    Route::get('/api/debug-tutors', function() {
        $tutors = \App\Models\Tutor::with(['accounts' => function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_name', 'GLS')->where('status', 'active');
        })
        ->where('status', 'active')
        ->take(3)
        ->get();
        
        $result = $tutors->map(function($tutor) {
            return [
                'id' => $tutor->tutorID,
                'username' => $tutor->tusername,
                'full_name' => $tutor->full_name,
                'status' => $tutor->status,
                'accounts' => $tutor->accounts->map(function($account) {
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


require __DIR__.'/auth.php';
