<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Http\Middleware\ProgressiveLockout;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        // Check if user is already authenticated and redirect to appropriate dashboard
        if (Auth::guard('supervisor')->check()) {
            return redirect('/dashboard')->with('status', 'Already logged in');
        }
        
        if (Auth::guard('tutor')->check()) {
            return redirect('/tutor_portal')->with('status', 'Already logged in');
        }
        
        // Clear any previous session data that might interfere
        session()->forget(['supervisor_logged_in', 'supervisor_id']);
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $loginId = $request->input('login_id');
        $password = $request->input('password');
        
        // TEMPORARY DEBUG - Check if password reset is coming here by mistake
        file_put_contents(storage_path('logs/password_reset_debug.txt'), 
            date('Y-m-d H:i:s') . " - AuthenticatedSessionController hit\n" .
            "Method: " . $request->method() . "\n" .
            "URL: " . $request->url() . "\n" .
            "All input: " . json_encode($request->all()) . "\n" .
            "---\n", 
            FILE_APPEND | LOCK_EX
        );
        
        // Check rate limiting before processing login
        $this->ensureIsNotRateLimited($request);

        if (preg_match('/^OGS-S\d+$/i', $loginId)) {
            // Supervisor login by supID
            $supervisor = \App\Models\Supervisor::where('supID', $loginId)->first();
            if ($supervisor && Auth::guard('supervisor')->attempt(['supID' => $loginId, 'password' => $password], $request->boolean('remember'))) {
                // Clear rate limiting on successful login
                RateLimiter::clear($this->throttleKey($request));
                // Clear progressive lockout count
                ProgressiveLockout::clearLockoutCount($request, $loginId);
                
                // Log successful supervisor login
                AuditLog::logEvent(
                    'login',
                    'supervisor',
                    $supervisor->supID,
                    $supervisor->semail,
                    $supervisor->sfname . ' ' . $supervisor->slname,
                    'Successful Supervisor Login',
                    "Supervisor {$supervisor->supID} ({$supervisor->sfname} {$supervisor->slname}) logged in successfully",
                    null,
                    'low',
                    true
                );
                
                $request->session()->regenerate();
                session(['supervisor_logged_in' => true, 'supervisor_id' => $loginId]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');

                return redirect('/dashboard');
            }
            
            // Log failed supervisor login attempt
            if ($supervisor) {
                AuditLog::logEvent(
                    'login_failed',
                    'supervisor',
                    $supervisor->supID,
                    $supervisor->semail,
                    $supervisor->sfname . ' ' . $supervisor->slname,
                    'Failed Supervisor Login',
                    "Failed login attempt for supervisor {$supervisor->supID} ({$supervisor->sfname} {$supervisor->slname})",
                    null,
                    'medium',
                    true
                );
            }
            
            // Rate limiting already handled in ensureIsNotRateLimited
            
            // Progressive lockout tracking
            ProgressiveLockout::incrementLockoutCount($request, $loginId);
            
            // Get remaining attempts and attempt message
            $remainingAttempts = $this->getRemainingAttempts($request);
            $attemptMessage = $this->getAttemptMessage($remainingAttempts);
            
            return back()->withErrors([
                'login_id' => 'Invalid supervisor ID or password.',
                'remaining_attempts' => $remainingAttempts,
                'attempt_message' => $attemptMessage,
            ]);
        } elseif (preg_match('/^OGS-T\d+$/i', $loginId)) {
            // Tutor login by tutorID
            $tutor = \App\Models\Tutor::where('tutorID', $loginId)->first();
            if ($tutor && Hash::check($password, $tutor->tpassword)) {
                // Clear rate limiting on successful login
                RateLimiter::clear($this->throttleKey($request));
                // Clear progressive lockout count
                ProgressiveLockout::clearLockoutCount($request, $loginId);
                
                Auth::guard('tutor')->login($tutor, $request->boolean('remember'));
                $request->session()->regenerate();
                session(['supervisor_logged_in' => false]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');

                return redirect('/tutor_portal');
            }
            
            // Rate limiting already handled in ensureIsNotRateLimited
            
            // Progressive lockout tracking
            ProgressiveLockout::incrementLockoutCount($request, $loginId);
            
            // Get remaining attempts and attempt message
            $remainingAttempts = $this->getRemainingAttempts($request);
            $attemptMessage = $this->getAttemptMessage($remainingAttempts);
            
            return back()->withErrors([
                'login_id' => 'Invalid tutor ID or password.',
                'remaining_attempts' => $remainingAttempts,
                'attempt_message' => $attemptMessage,
            ]);
        } else {
            // Fallback: try email for tutor login
            $tutor = \App\Models\Tutor::where('email', $loginId)->first();
            if ($tutor && Hash::check($password, $tutor->tpassword)) {
                // Clear rate limiting on successful login
                RateLimiter::clear($this->throttleKey($request));
                // Clear progressive lockout count
                ProgressiveLockout::clearLockoutCount($request, $loginId);
                
                Auth::guard('tutor')->login($tutor, $request->boolean('remember'));
                $request->session()->regenerate();
                session(['supervisor_logged_in' => false]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');

                return redirect('/tutor_portal');
            }
            // Fallback: try supervisor email
            Log::info('Attempting supervisor login with email', ['email' => $loginId, 'password_length' => strlen($password)]);
            
            // First, try to find the supervisor manually
            $supervisor = \App\Models\Supervisor::where('semail', $loginId)->first();
            Log::info('Supervisor manual lookup', ['found' => $supervisor ? 'yes' : 'no', 'email' => $loginId]);
            
            if ($supervisor) {
                Log::info('Supervisor found', ['id' => $supervisor->supID, 'email' => $supervisor->semail]);
                Log::info('Password check manually', ['hash_check' => Hash::check($password, $supervisor->password)]);
            }
            
            $attemptResult = Auth::guard('supervisor')->attempt(['semail' => $loginId, 'password' => $password], $request->boolean('remember'));
            Log::info('Supervisor login attempt result', ['result' => $attemptResult, 'email' => $loginId]);
            
            if ($attemptResult) {
                // Clear rate limiting on successful login
                RateLimiter::clear($this->throttleKey($request));
                // Clear progressive lockout count
                ProgressiveLockout::clearLockoutCount($request, $loginId);
                
                $request->session()->regenerate();
                $supervisor = Auth::guard('supervisor')->user();
                session([
                    'supervisor_logged_in' => true,
                    'supervisor_id' => $supervisor->supID
                ]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');

                return redirect('/dashboard');
            }
            
            // Rate limiting already handled in ensureIsNotRateLimited
            
            // Progressive lockout tracking
            ProgressiveLockout::incrementLockoutCount($request, $loginId);
            
            // Get remaining attempts and attempt message
            $remainingAttempts = $this->getRemainingAttempts($request);
            $attemptMessage = $this->getAttemptMessage($remainingAttempts);
            
            return back()->withErrors([
                'login_id' => 'Invalid credentials.',
                'remaining_attempts' => $remainingAttempts,
                'attempt_message' => $attemptMessage,
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout event before clearing authentication
        $user = null;
        $userType = null;
        $userId = null;
        $userEmail = null;
        $userName = null;
        
        if (Auth::guard('supervisor')->check()) {
            $user = Auth::guard('supervisor')->user();
            $userType = 'supervisor';
            $userId = $user->supID;
            $userEmail = $user->semail;
            $userName = $user->sfname . ' ' . $user->slname;
        } elseif (Auth::guard('tutor')->check()) {
            $user = Auth::guard('tutor')->user();
            $userType = 'tutor';
            $userId = $user->tutorID;
            $userEmail = $user->email;
            $userName = $user->fname . ' ' . $user->lname;
        }
        
        if ($user) {
            AuditLog::logEvent(
                'logout',
                $userType,
                $userId,
                $userEmail,
                $userName,
                'User Logout',
                "{$userType} {$userId} ({$userName}) logged out successfully",
                null,
                'low',
                false
            );
        }
        
        // Logout from both guards to ensure complete logout
        Auth::guard('web')->logout();
        Auth::guard('supervisor')->logout();

        // Clear session data
        $request->session()->forget(['supervisor_logged_in', 'supervisor_id', 'url.intended']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = config('security.login.max_attempts', 3);
        $decayMinutes = config('security.login.decay_minutes', 15);
        
        // First, increment the attempt counter
        RateLimiter::hit($this->throttleKey($request), $decayMinutes * 60);
        
        // Then check if they've exceeded the limit
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            
            // Check if progressive lockout should suggest password reset
            $loginId = $request->input('login_id');
            $lockoutCount = Cache::get("lockout_count_progressive_lockout_" . md5(strtolower($loginId) . '|' . $request->ip()), 0);
            
            // Show helpful message to guide users to existing Forgot Password button
            $request->session()->flash('suggest_password_reset', true);
            $request->session()->flash('lockout_count', $lockoutCount);
            
            // Better time display logic
            if ($seconds < 60) {
                $timeMessage = "Please try again in {$seconds} second(s).";
            } else {
                $minutes = ceil($seconds / 60);
                $timeMessage = "Please try again in {$minutes} minute(s).";
            }

            throw ValidationException::withMessages([
                'login_id' => "Too many login attempts. {$timeMessage}",
                'throttled' => true,
                'wait_time' => $seconds,
            ]);
        }
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('login_id')).'|'.$request->ip());
    }

    /**
     * Get remaining login attempts for the user.
     */
    public function getRemainingAttempts(Request $request): int
    {
        $maxAttempts = config('security.login.max_attempts', 3);
        $attempts = RateLimiter::attempts($this->throttleKey($request));
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get descriptive attempt message.
     */
    public function getAttemptMessage(int $remainingAttempts): string
    {
        switch ($remainingAttempts) {
            case 3:
                return ""; // First attempt - no message
            case 2:
                return "First attempt - 2 attempts remaining";
            case 1:
                return "Second attempt - 1 attempt remaining";
            case 0:
                return "Last attempt - Account will be locked after this";
            default:
                return "Invalid attempt count";
        }
    }
}