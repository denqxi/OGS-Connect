<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ImportController;
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

Route::get('/scheduling', [ScheduleController::class, 'index'])->name('schedules.index');

Route::get('/class-scheduling', function () {
    return view('schedules.class-scheduling');
})->name('class-scheduling');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');

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

// Auto-assign routes

// API route to get available tutors
Route::get('/api/available-tutors', [ScheduleController::class, 'getAvailableTutors'])->name('api.available-tutors');

// API route to get class tutors (main and backup)
Route::get('/api/class-tutors/{classId}', [ScheduleController::class, 'getClassTutors'])->name('api.class-tutors');

// API route to save tutor assignments
Route::post('/api/save-tutor-assignments', [ScheduleController::class, 'saveTutorAssignments'])->name('api.save-tutor-assignments');

// API route for real-time search
Route::get('/api/search-schedules', [ScheduleController::class, 'searchSchedules'])->name('api.search-schedules');

// Auto-assign routes
Route::post('/schedules/auto-assign', [ScheduleController::class, 'autoAssignTutors'])->name('schedules.auto-assign');
Route::post('/schedules/auto-assign/{date}', [ScheduleController::class, 'autoAssignTutorsForDate'])->name('schedules.auto-assign.date');
Route::post('/schedules/auto-assign/{date}/{day}', [ScheduleController::class, 'autoAssignTutorsForSpecific'])->name('schedules.auto-assign.specific');
Route::post('/schedules/auto-assign-class/{class}', [ScheduleController::class, 'autoAssignTutorsForClass'])->name('schedules.auto-assign.class');
Route::delete('/schedules/remove-assignment/{assignment}', [ScheduleController::class, 'removeTutorAssignment'])->name('schedules.remove-assignment');

// Schedule status routes
Route::post('/schedules/save-as-partial/{date}', [ScheduleController::class, 'saveAsPartial'])->name('schedules.save-as-partial');
Route::post('/schedules/save-as-final/{date}', [ScheduleController::class, 'saveAsFinal'])->name('schedules.save-as-final');

// Export routes
Route::get('/schedules/export-tentative', [ScheduleController::class, 'exportTentativeSchedule'])->name('schedules.export-tentative');
Route::get('/schedules/export-final', [ScheduleController::class, 'exportFinalSchedule'])->name('schedules.export-final');
Route::post('/schedules/export-selected', [ScheduleController::class, 'exportSelectedSchedules'])->name('schedules.export-selected');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/tutors/{tutor}/toggle-status', [ScheduleController::class, 'toggleTutorStatus'])->name('tutors.toggle-status');

require __DIR__.'/auth.php';
