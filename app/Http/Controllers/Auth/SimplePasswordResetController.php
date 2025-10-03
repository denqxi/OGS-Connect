<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class SimplePasswordResetController extends Controller
{
    /**
     * Show the password reset request form.
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the password reset request with security question verification.
     */
    public function requestReset(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'user_type' => 'required|in:tutor,supervisor',
            'security_question' => 'required|string',
            'security_answer1' => 'required|string|min:2',
            'security_question2' => 'required|string',
            'security_answer2' => 'required|string|min:2',
        ], [
            'username.required' => 'Username or ID is required.',
            'user_type.required' => 'Account type is required.',
            'user_type.in' => 'Invalid account type.',
            'security_question.required' => 'Please select a security question.',
            'security_answer1.required' => 'Please provide an answer to the first security question.',
            'security_answer1.min' => 'Security answer must be at least 2 characters.',
            'security_question2.required' => 'Please select a second security question.',
            'security_answer2.required' => 'Please provide an answer to the second security question.',
            'security_answer2.min' => 'Security answer must be at least 2 characters.',
        ]);

        $username = $request->username;
        $userType = $request->user_type;
        $securityQuestion = $request->security_question;
        $securityAnswer1 = strtolower(trim($request->security_answer1));
        $securityQuestion2 = $request->security_question2;
        $securityAnswer2 = strtolower(trim($request->security_answer2));

        // Auto-detect account type by searching both tutor and supervisor tables
        $user = null;
        $detectedUserType = null;

        // First, try to find as tutor
        $tutor = Tutor::where('email', $username)
                     ->orWhere('tutorID', $username)
                     ->orWhere('tusername', $username)
                     ->first();
        
        if ($tutor) {
            $user = $tutor;
            $detectedUserType = 'tutor';
        } else {
            // If not found as tutor, try supervisor
            $supervisor = Supervisor::where('semail', $username)
                                   ->orWhere('supID', $username)
                                   ->first();
            
            if ($supervisor) {
                $user = $supervisor;
                $detectedUserType = 'supervisor';
            }
        }

        if (!$user) {
            return back()->withErrors([
                'username' => 'No account found with the provided email or ID.',
            ])->withInput();
        }

        // Verify that the detected user type matches the submitted user type
        if ($detectedUserType !== $userType) {
            return back()->withErrors([
                'username' => 'Account type mismatch. Please try again.',
            ])->withInput();
        }

        // Check if user has security questions set up
        $securityQuestions = SecurityQuestion::getAllForUser($userType, $user->getKey());
        
        if ($securityQuestions->count() < 2) {
            return back()->withErrors([
                'username' => 'Not enough security questions set up for this account. Please contact support.',
            ])->withInput();
        }

        // Find the matching security question records
        $question1Record = $securityQuestions->where('question', $securityQuestion)->first();
        $question2Record = $securityQuestions->where('question', $securityQuestion2)->first();

        if (!$question1Record || !$question2Record) {
            return back()->withErrors([
                'security_question' => 'Selected security questions do not match the ones set up for this account.',
            ])->withInput();
        }

        // Verify both answers
        if (!$question1Record->verifyAnswer($securityAnswer1)) {
            return back()->withErrors([
                'security_answer1' => 'Incorrect answer to the first security question. Please try again.',
            ])->withInput();
        }

        if (!$question2Record->verifyAnswer($securityAnswer2)) {
            return back()->withErrors([
                'security_answer2' => 'Incorrect answer to the second security question. Please try again.',
            ])->withInput();
        }

        // Store user info in session for password reset
        Session::put('password_reset_user', [
            'id' => $user->getKey(),
            'type' => $userType,
            'username' => $username,
            'name' => $userType === 'tutor' ? $user->getFullNameAttribute() : $user->getFullNameAttribute(),
        ]);

        // Redirect to password reset form
        return redirect()->route('password.reset.form')
                        ->with('status', 'Security question verified! Please set your new password.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm()
    {
        $userInfo = Session::get('password_reset_user');
        
        if (!$userInfo) {
            return redirect()->route('password.request')
                           ->withErrors(['error' => 'Password reset session expired. Please try again.']);
        }

        return view('auth.reset-password', [
            'username' => $userInfo['username'],
            'user_type' => $userInfo['type'],
            'user_name' => $userInfo['name'],
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
            'username' => 'required|string',
            'user_type' => 'required|in:tutor,supervisor',
        ], [
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $username = $request->username;
        $userType = $request->user_type;

        // Find the user based on type and email/ID
        $user = null;
        if ($userType === 'tutor') {
            $user = Tutor::where('email', $username)
                        ->orWhere('tutorID', $username)
                        ->orWhere('tusername', $username)
                        ->first();
        } elseif ($userType === 'supervisor') {
            $user = Supervisor::where('semail', $username)
                             ->orWhere('supID', $username)
                             ->first();
        }

        if (!$user) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'User not found. Please try again.']);
        }

        // Update the password
        if ($userType === 'tutor') {
            $user->update(['tpassword' => Hash::make($request->password)]);
        } else {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('login')
                        ->with('status', 'Password updated successfully! You can now log in with your new password.');
    }

    /**
     * Get security question for a user via AJAX
     */
    public function getSecurityQuestion(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255',
            ]);

            $username = $request->username;

            // Auto-detect account type by searching both tutor and supervisor tables
            $user = null;
            $userType = null;

            // First, try to find as tutor
            $tutor = Tutor::where('email', $username)
                         ->orWhere('tutorID', $username)
                         ->orWhere('tusername', $username)
                         ->first();
            
            if ($tutor) {
                $user = $tutor;
                $userType = 'tutor';
            } else {
                // If not found as tutor, try supervisor
                $supervisor = Supervisor::where('semail', $username)
                                       ->orWhere('supID', $username)
                                       ->first();
                
                if ($supervisor) {
                    $user = $supervisor;
                    $userType = 'supervisor';
                }
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with the provided email or ID'
                ]);
            }

            // Get security questions for the user
            $securityQuestions = SecurityQuestion::getAllForUser($userType, $user->getKey());

            if ($securityQuestions->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough security questions set up for this account. Please contact administrator.'
                ]);
            }

            return response()->json([
                'success' => true,
                'user_type' => $userType,
                'questions' => $securityQuestions->pluck('question')->toArray()
            ]);

        } catch (\Exception $e) {
            \Log::error('Security question API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
