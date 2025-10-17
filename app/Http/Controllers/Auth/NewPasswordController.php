<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ProgressiveLockout;
use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): RedirectResponse
    {
        // Redirect to login page with reset parameters as URL params
        return redirect()->route('login', [
            'reset_mode' => 'true',
            'token' => $request->route('token'),
            'email' => $request->email
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        // TEMPORARY DEBUG - Log all incoming requests
        file_put_contents(storage_path('logs/password_reset_debug.txt'), 
            date('Y-m-d H:i:s') . " - NewPasswordController hit\n" .
            "Method: " . $request->method() . "\n" .
            "URL: " . $request->url() . "\n" .
            "All input: " . json_encode($request->all()) . "\n" .
            "---\n", 
            FILE_APPEND | LOCK_EX
        );

        Log::info('Password reset attempt', [
            'email' => $request->email,
            'token_provided' => !empty($request->token),
            'password_provided' => !empty($request->password),
            'password_confirmation_provided' => !empty($request->password_confirmation),
            'all_input' => $request->all(),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ], [
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.mixed_case' => 'Password must contain both uppercase and lowercase letters.',
            'password.letters' => 'Password must contain letters.',
            'password.numbers' => 'Password must contain numbers.',
            'password.symbols' => 'Password must contain symbols.',
            'password.uncompromised' => 'Password has appeared in data breaches and cannot be used.',
        ]);

        // Check if the token exists and is valid
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            Log::warning('Password reset token not found', ['email' => $request->email]);
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        // Check if token matches (it's hashed in database)
        if (!Hash::check($request->token, $passwordReset->token)) {
            Log::warning('Password reset token mismatch', ['email' => $request->email]);
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        // Check if token is expired (60 minutes)
        $tokenAge = now()->diffInMinutes($passwordReset->created_at);
        if ($tokenAge > 60) {
            Log::warning('Password reset token expired', [
                'email' => $request->email,
                'token_age_minutes' => $tokenAge
            ]);
            
            // Clean up expired token
            DB::table('password_resets')->where('email', $request->email)->delete();
            
            return back()->withErrors(['email' => 'This password reset token has expired. Please request a new one.']);
        }

        // Find the user in either supervisors or tutors table
        $user = null;
        $userType = null;
        $cleanEmail = trim($request->email);

        // Search in supervisors table first
        $supervisor = Supervisor::where(function($query) use ($cleanEmail) {
            $query->where('semail', $cleanEmail)
                  ->orWhere(DB::raw('TRIM(REPLACE(REPLACE(semail, CHAR(10), ""), CHAR(13), ""))'), $cleanEmail);
        })->first();

        if ($supervisor) {
            $user = $supervisor;
            $userType = 'supervisor';
            Log::info('User found in supervisors table for password reset', ['user_id' => $supervisor->supID]);
        } else {
            // Search in tutors table
            $tutor = Tutor::where(function($query) use ($cleanEmail) {
                $query->where('email', $cleanEmail)
                      ->orWhere(DB::raw('TRIM(REPLACE(REPLACE(email, CHAR(10), ""), CHAR(13), ""))'), $cleanEmail);
            })->first();

            if ($tutor) {
                $user = $tutor;
                $userType = 'tutor';
                Log::info('User found in tutors table for password reset', ['user_id' => $tutor->tutorID]);
            }
        }

        if (!$user) {
            Log::error('User not found for password reset', ['email' => $request->email]);
            return back()->withErrors(['email' => 'We cannot find a user with that email address.']);
        }

        // Update the user's password
        try {
            if ($userType === 'supervisor') {
                // Don't use Hash::make() here because the model's 'hashed' cast will handle it
                $user->update([
                    'password' => $request->password
                ]);
            } else { // tutor
                // Tutors don't have 'hashed' cast, so we manually hash
                $user->update([
                    'tpassword' => Hash::make($request->password)
                ]);
            }

            Log::info('Password successfully reset', [
                'user_type' => $userType,
                'user_id' => $user->supID ?? $user->tutorID,
                'email' => $request->email
            ]);

            // Clear rate limiting and progressive lockout
            $loginId = $request->email;
            if ($loginId) {
                $throttleKey = Str::transliterate(Str::lower($loginId) . '|' . $request->ip());
                RateLimiter::clear($throttleKey);
                ProgressiveLockout::clearLockoutCount($request, $loginId);
            }

            // Delete the used token
            DB::table('password_resets')->where('email', $request->email)->delete();

            return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in with your new password.');

        } catch (\Exception $e) {
            Log::error('Failed to update password', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'user_type' => $userType
            ]);

            return back()->withErrors(['password' => 'Failed to update password. Please try again.']);
        }
    }
}
