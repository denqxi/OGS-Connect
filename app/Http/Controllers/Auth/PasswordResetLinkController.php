<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'user_type' => 'required|in:tutor,supervisor',
        ], [
            'username.required' => 'Username or ID is required.',
            'user_type.required' => 'Please select your account type.',
            'user_type.in' => 'Invalid account type selected.',
        ]);

        $username = $request->username;
        $userType = $request->user_type;

        // Find the user based on type and username
        $user = null;
        if ($userType === 'tutor') {
            $user = Tutor::where('tusername', $username)
                        ->orWhere('tutorID', $username)
                        ->first();
        } elseif ($userType === 'supervisor') {
            $user = Supervisor::where('susername', $username)
                             ->orWhere('supID', $username)
                             ->first();
        }

        if (!$user) {
            return back()->withErrors([
                'username' => 'No account found with the provided username or ID.',
            ])->withInput();
        }

        // Get the email for the user
        $email = null;
        if ($userType === 'tutor') {
            $email = $user->email;
        } elseif ($userType === 'supervisor') {
            $email = $user->semail;
        }

        if (!$email) {
            return back()->withErrors([
                'username' => 'No email address found for this account. Please contact support.',
            ])->withInput();
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink([
            'email' => $email
        ]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', 'Password reset link sent to your email address.')
                    : back()->withInput($request->only('username', 'user_type'))
                        ->withErrors(['username' => 'Unable to send password reset link. Please try again.']);
    }
}
