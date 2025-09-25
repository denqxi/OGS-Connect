<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DailyDataController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('landing');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

//APPLICATION FORM ROUTES START

Route::get('/application-form', function () {
    return view('application_form.application');
})->name('application.form');

Route::get('/scheduling', function () {
    return view('schedules.index');
})->name('schedules.index');

Route::get('/class-scheduling', function () {
    return view('schedules.class-scheduling');
})->name('class-scheduling');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/import-test', function () {
    return view('import'); // your upload form
});

Route::post('/import-upload', [ImportController::class, 'upload'])->name('import.upload');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
