<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API route for getting security questions (no auth required for password reset)
Route::post('/get-security-question', [\App\Http\Controllers\Auth\SimplePasswordResetController::class, 'getSecurityQuestion']);
