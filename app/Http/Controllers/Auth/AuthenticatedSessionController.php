<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Supervisor;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $userRole = $request->input('user_role', 'auto');

        // Determine which role to authenticate
        $role = $this->determineRole($loginId, $userRole);

        // Try authentication based on role
        if ($role === 'supervisor') {
            $result = $this->authenticateSupervisor($loginId, $password, $request);
            if ($result) {
                return $result;
            }
        } elseif ($role === 'tutor') {
            $result = $this->authenticateTutor($loginId, $password, $request);
            if ($result) {
                return $result;
            }
        } else {
            // Auto-detect: try supervisor first, then tutor
            $result = $this->authenticateSupervisor($loginId, $password, $request);
            if ($result) {
                return $result;
            }
            
            $result = $this->authenticateTutor($loginId, $password, $request);
            if ($result) {
                return $result;
            }
        }

        // Authentication failed
        return back()->withErrors([
            'login_id' => 'Invalid credentials. Please check your ID/email and password.',
        ])->withInput($request->only('login_id', 'user_role'));
    }

    /**
     * Determine the role based on input and form selection
     */
    private function determineRole(string $loginId, string $userRole): ?string
    {
        if ($userRole === 'admin_supervisor') {
            return 'supervisor';
        }
        
        if ($userRole === 'tutor') {
            return 'tutor';
        }

        // Auto-detect based on ID format
        if (preg_match('/^OGS-S\d+$/i', $loginId)) {
            return 'supervisor';
        }
        
        if (preg_match('/^OGS-T\d+$/i', $loginId)) {
            return 'tutor';
        }

        return null; // Will try both
    }

    /**
     * Authenticate supervisor
     */
    private function authenticateSupervisor(string $loginId, string $password, Request $request): ?RedirectResponse
    {
        $supervisor = null;

        // Try by supervisor ID (OGS-S format)
        if (preg_match('/^OGS-S\d+$/i', $loginId)) {
            $supervisor = Supervisor::where('supID', $loginId)->first();
        }
        
        // Try by email if not found
        if (!$supervisor) {
            $supervisor = Supervisor::where('email', $loginId)->first();
        }

        if (!$supervisor) {
            return null;
        }

        // Check password
        if (!$supervisor->password || !Hash::check($password, $supervisor->password)) {
            return null;
        }

        // Login successful
        Auth::guard('supervisor')->login($supervisor, $request->boolean('remember'));
        $request->session()->regenerate();
        session([
            'supervisor_logged_in' => true,
            'supervisor_id' => $supervisor->supID ?? $supervisor->supervisor_id
        ]);
        $request->session()->forget('url.intended');

        return redirect()->intended('/dashboard');
    }

    /**
     * Authenticate tutor
     */
    private function authenticateTutor(string $loginId, string $password, Request $request): ?RedirectResponse
    {
        $tutor = null;

        // Try by tutor ID (OGS-T format)
        if (preg_match('/^OGS-T\d+$/i', $loginId)) {
            $tutor = Tutor::where('tutorID', $loginId)->first();
        }
        
        // Try by email if not found
        if (!$tutor) {
            $tutor = Tutor::where('email', $loginId)->first();
        }

        if (!$tutor) {
            return null;
        }

        // Check password
        if (!$tutor->tpassword || !Hash::check($password, $tutor->tpassword)) {
            return null;
        }

        // Login successful
        Auth::guard('tutor')->login($tutor, $request->boolean('remember'));
        $request->session()->regenerate();
        session(['supervisor_logged_in' => false]);
        $request->session()->forget('url.intended');

        return redirect()->intended('/tutor_portal');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout from all guards to ensure complete logout
        Auth::guard('web')->logout();
        Auth::guard('supervisor')->logout();
        Auth::guard('tutor')->logout();

        // Clear session data
        $request->session()->forget(['supervisor_logged_in', 'supervisor_id', 'url.intended']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
