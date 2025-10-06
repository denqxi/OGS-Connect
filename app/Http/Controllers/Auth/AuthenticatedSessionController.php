<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $tutor = \App\Models\Tutor::where('tutorID', $loginId)->first();
            if ($tutor && \Hash::check($password, $tutor->tpassword)) {
                Auth::guard('tutor')->login($tutor, $request->boolean('remember'));
                $request->session()->regenerate();
                session(['supervisor_logged_in' => false]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');


                return redirect('/tutor_portal');
            }
            return back()->withErrors([
                'login_id' => 'Invalid tutor ID or password.',
            ]);
        } else {
            // Fallback: try email for tutor login
            $tutor = \App\Models\Tutor::where('email', $loginId)->first();
            if ($tutor && \Hash::check($password, $tutor->tpassword)) {
                Auth::guard('tutor')->login($tutor, $request->boolean('remember'));
                $request->session()->regenerate();
                session(['supervisor_logged_in' => false]);
                // Clear any intended URL to ensure fresh start
                $request->session()->forget('url.intended');


                return redirect('/tutor_portal');
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

        return redirect('/login');
    }
}