<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $loginId = $request->input('login_id');
        $password = $request->input('password');

        // Debug logging
        Log::info('Login attempt started', [
            'login_id' => $loginId,
            'password_length' => strlen($password),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if (preg_match('/^OGS-S\d+$/i', $loginId)) {
            // Supervisor login by supID
            $supervisor = \App\Models\Supervisor::where('supID', $loginId)->first();
            if ($supervisor && Auth::guard('supervisor')->attempt(['supID' => $loginId, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                session(['supervisor_logged_in' => true, 'supervisor_id' => $loginId]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');
                
                
                return redirect('/dashboard');
            }
            return back()->withErrors([
                'login_id' => 'Invalid supervisor ID or password.',
            ]);
        } elseif (preg_match('/^OGS-T\d+$/i', $loginId)) {
            // Tutor login by tutorID
            Log::info('Tutor ID login attempt', ['tutor_id' => $loginId]);
            
            $tutor = \App\Models\Tutor::where('tutorID', $loginId)->first();
            
            if ($tutor) {
                Log::info('Tutor found in database', [
                    'tutor_id' => $tutor->tutorID,
                    'email' => $tutor->email,
                    'username' => $tutor->tusername,
                    'status' => $tutor->status,
                    'has_password' => !empty($tutor->tpassword)
                ]);
                
                if ($tutor->status !== 'active') {
                    Log::warning('Tutor account is not active', [
                        'tutor_id' => $tutor->tutorID,
                        'status' => $tutor->status
                    ]);
                    return back()->withErrors([
                        'login_id' => 'Account is not active. Please contact administrator.',
                    ]);
                }
                
                $passwordCheck = \Hash::check($password, $tutor->tpassword);
                Log::info('Password verification result', [
                    'tutor_id' => $tutor->tutorID,
                    'password_matches' => $passwordCheck,
                    'password_hash' => substr($tutor->tpassword, 0, 20) . '...'
                ]);
                
                if ($passwordCheck) {
                    Log::info('Tutor login successful', ['tutor_id' => $tutor->tutorID]);
                    Auth::guard('web')->login($tutor, $request->boolean('remember'));
                    $request->session()->regenerate();
                    session(['supervisor_logged_in' => false]);
                    // Clear any intended URL to ensure fresh start
                    $request->session()->forget('url.intended');
                    
                    return redirect('/dashboard');
                } else {
                    Log::warning('Tutor password mismatch', [
                        'tutor_id' => $tutor->tutorID,
                        'provided_password' => $password
                    ]);
                }
            } else {
                Log::warning('Tutor not found in database', ['tutor_id' => $loginId]);
            }
            
            return back()->withErrors([
                'login_id' => 'Invalid tutor ID or password.',
            ]);
        } else {
            // Fallback: try email for tutor login
            Log::info('Email login attempt', ['email' => $loginId]);
            
            $tutor = \App\Models\Tutor::where('email', $loginId)->first();
            
            if ($tutor) {
                Log::info('Tutor found by email', [
                    'email' => $tutor->email,
                    'tutor_id' => $tutor->tutorID,
                    'status' => $tutor->status
                ]);
                
                if ($tutor->status !== 'active') {
                    Log::warning('Tutor account is not active (email login)', [
                        'email' => $tutor->email,
                        'status' => $tutor->status
                    ]);
                    return back()->withErrors([
                        'login_id' => 'Account is not active. Please contact administrator.',
                    ]);
                }
                
                $passwordCheck = \Hash::check($password, $tutor->tpassword);
                Log::info('Email login password verification', [
                    'email' => $tutor->email,
                    'password_matches' => $passwordCheck
                ]);
                
                if ($passwordCheck) {
                    Log::info('Tutor email login successful', ['email' => $tutor->email]);
                    Auth::guard('web')->login($tutor, $request->boolean('remember'));
                    $request->session()->regenerate();
                    session(['supervisor_logged_in' => false]);
                    // Clear any intended URL to ensure fresh start
                    $request->session()->forget('url.intended');
                    
                    return redirect('/dashboard');
                } else {
                    Log::warning('Tutor email password mismatch', [
                        'email' => $tutor->email,
                        'provided_password' => $password
                    ]);
                }
            } else {
                Log::warning('Tutor not found by email', ['email' => $loginId]);
            }
            // Fallback: try supervisor email
            if (Auth::guard('supervisor')->attempt(['semail' => $loginId, 'password' => $password], $request->boolean('remember'))) {
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
            Log::warning('All login attempts failed', [
                'login_id' => $loginId,
                'attempted_tutor_email' => true,
                'attempted_supervisor_email' => true
            ]);
            
            return back()->withErrors([
                'login_id' => 'Invalid credentials.',
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout from both guards to ensure complete logout
        Auth::guard('web')->logout();
        Auth::guard('supervisor')->logout();

        // Clear session data
        $request->session()->forget(['supervisor_logged_in', 'supervisor_id', 'url.intended']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with cache control headers to prevent back button access
        return redirect('/login')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
