<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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

        // Check if this is an AJAX request
        $isAjax = $request->expectsJson();

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
                     ->orWhere('username', $username)
                     ->first();
        
        if ($tutor) {
            $user = $tutor;
            $detectedUserType = 'tutor';
        } else {
            // If not found as tutor, try supervisor
            $supervisor = Supervisor::where('email', $username)
                                   ->orWhere('supID', $username)
                                   ->first();
            
            if ($supervisor) {
                $user = $supervisor;
                $detectedUserType = 'supervisor';
            }
        }

        if (!$user) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'No account found with the provided email or ID.'], 404);
            }
            return back()->withErrors(['username' => 'No account found with the provided email or ID.'])->withInput();
        }

        // Verify that the detected user type matches the submitted user type
        if ($detectedUserType !== $userType) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Account type mismatch. Please try again.'], 422);
            }
            return back()->withErrors(['username' => 'Account type mismatch. Please try again.'])->withInput();
        }

        // Check if user has security questions set up
        $securityQuestions = SecurityQuestion::getAllForUser($userType, $user->getKey());
        
        if ($securityQuestions->count() < 2) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Not enough security questions set up for this account. Please contact support.'], 422);
            }
            return back()->withErrors(['username' => 'Not enough security questions set up for this account. Please contact support.'])->withInput();
        }

        // Find the matching security question records
        $question1Record = $securityQuestions->where('question', $securityQuestion)->first();
        $question2Record = $securityQuestions->where('question', $securityQuestion2)->first();

        if (!$question1Record || !$question2Record) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Selected security questions do not match the ones set up for this account.'], 422);
            }
            return back()->withErrors(['security_question' => 'Selected security questions do not match the ones set up for this account.'])->withInput();
        }

        // Verify both answers
        \Log::info('Verifying security questions', [
            'user_type' => $userType,
            'user_id' => $user->getKey(),
            'question1' => $securityQuestion,
            'question2' => $securityQuestion2
        ]);
        
        if (!$question1Record->verifyAnswer($securityAnswer1)) {
            \Log::warning('First security question verification failed', [
                'user_type' => $userType,
                'user_id' => $user->getKey()
            ]);
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Incorrect answer to the first security question. Please try again.'], 422);
            }
            return back()->withErrors(['security_answer1' => 'Incorrect answer to the first security question. Please try again.'])->withInput();
        }

        if (!$question2Record->verifyAnswer($securityAnswer2)) {
            \Log::warning('Second security question verification failed', [
                'user_type' => $userType,
                'user_id' => $user->getKey()
            ]);
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Incorrect answer to the second security question. Please try again.'], 422);
            }
            return back()->withErrors(['security_answer2' => 'Incorrect answer to the second security question. Please try again.'])->withInput();
        }
        
        \Log::info('Security questions verified successfully', [
            'user_type' => $userType,
            'user_id' => $user->getKey()
        ]);

        // Store user info in session for password reset
        Session::put('password_reset_user', [
            'id' => $user->getKey(),
            'type' => $userType,
            'username' => $username,
            'name' => $userType === 'tutor' ? $user->getFullNameAttribute() : $user->getFullNameAttribute(),
        ]);

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => 'Security questions verified! Redirecting...']);
        }

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

        // Ensure the password reset was authorized via server-side verification
        $sessionUser = Session::get('password_reset_user');
        if (!$sessionUser || ($sessionUser['username'] ?? '') !== $username || ($sessionUser['type'] ?? '') !== $userType) {
            return redirect()->route('password.request')
                           ->withErrors(['error' => 'Unauthorized password reset attempt or session expired. Please verify first.']);
        }

        // Find the user based on type and email/ID
        $user = null;
        if ($userType === 'tutor') {
            $user = Tutor::where('email', $username)
                        ->orWhere('tutorID', $username)
                        ->orWhere('username', $username)
                        ->first();
        } elseif ($userType === 'supervisor') {
            $user = Supervisor::where('email', $username)
                             ->orWhere('supID', $username)
                             ->first();
        }

        if (!$user) {
            return redirect()->route('login')
                           ->withErrors(['error' => 'User not found. Please try again.']);
        }

        // Update the password
        if ($userType === 'tutor') {
            $user->update(['password' => Hash::make($request->password)]);
        } else {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Clear the password reset session marker
        Session::forget('password_reset_user');

        return redirect()->route('login')
                        ->with('status', 'Password updated successfully! You can now log in with your new password.');
    }

    /**
     * Get security question for a user via AJAX
     */
    public function getSecurityQuestion(Request $request)
    {
        try {
            // Handle both JSON and form data
            $username = $request->input('username') ?? $request->json('username');
            
            if (!$username) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username is required'
                ], 400);
            }

            // Auto-detect account type by searching both tutor and supervisor tables
            $user = null;
            $userType = null;

            // First, try to find as tutor
            $tutor = Tutor::where('email', $username)
                         ->orWhere('tutorID', $username)
                         ->orWhere('username', $username)
                         ->first();
            
            if ($tutor) {
                $user = $tutor;
                $userType = 'tutor';
            } else {
                // If not found as tutor, try supervisor
                $supervisor = Supervisor::where('email', $username)
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

    /**
     * Send an email OTP for password reset.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        $username = $request->input('username');

        // Auto-detect account type and user
        $user = null;
        $userType = null;

        $tutor = Tutor::where('email', $username)
                     ->orWhere('tutorID', $username)
                     ->orWhere('username', $username)
                     ->first();
        if ($tutor) {
            $user = $tutor;
            $userType = 'tutor';
        } else {
            $supervisor = Supervisor::where('email', $username)
                                   ->orWhere('supID', $username)
                                   ->first();
            if ($supervisor) {
                $user = $supervisor;
                $userType = 'supervisor';
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found for the provided identifier.'], 404);
        }

        // Generate OTP and store hashed in cache for 10 minutes
        $otp = strval(mt_rand(100000, 999999));
        $cacheKey = "password_reset_otp:{$userType}:{$user->getKey()}";
        Cache::put($cacheKey, Hash::make($otp), now()->addMinutes(10));

        try {
            // Send OTP by email
            Mail::to($user->email)->send(new \App\Mail\PasswordResetOtpMail($otp, $user));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again later.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'OTP sent to the registered email address.']);
    }

    /**
     * Verify OTP and create reset session.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'otp' => 'required|string|min:6|max:6'
        ]);

        $username = $request->input('username');
        $otp = $request->input('otp');
        $isAjax = $request->expectsJson();

        // Find user
        $user = null;
        $userType = null;

        $tutor = Tutor::where('email', $username)
                     ->orWhere('tutorID', $username)
                     ->orWhere('username', $username)
                     ->first();
        if ($tutor) {
            $user = $tutor;
            $userType = 'tutor';
        } else {
            $supervisor = Supervisor::where('email', $username)
                                   ->orWhere('supID', $username)
                                   ->first();
            if ($supervisor) {
                $user = $supervisor;
                $userType = 'supervisor';
            }
        }

        if (!$user) {
            if ($isAjax) return response()->json(['success' => false, 'message' => 'No account found for the provided identifier.'], 404);
            return back()->withErrors(['username' => 'No account found for the provided identifier.']);
        }

        $cacheKey = "password_reset_otp:{$userType}:{$user->getKey()}";
        $hash = Cache::get($cacheKey);
        if (!$hash) {
            if ($isAjax) return response()->json(['success' => false, 'message' => 'OTP expired or not found. Request a new one.'], 422);
            return back()->withErrors(['otp' => 'OTP expired or not found. Request a new one.']);
        }

        if (!Hash::check($otp, $hash)) {
            if ($isAjax) return response()->json(['success' => false, 'message' => 'Invalid OTP. Please check and try again.'], 422);
            return back()->withErrors(['otp' => 'Invalid OTP. Please check and try again.']);
        }

        // OTP valid — store password reset session and clear cache
        Session::put('password_reset_user', [
            'id' => $user->getKey(),
            'type' => $userType,
            'username' => $username,
            'name' => method_exists($user, 'getFullNameAttribute') ? $user->getFullNameAttribute() : ($user->name ?? $username),
        ]);

        Cache::forget($cacheKey);

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => 'OTP verified! Ready to set new password.']);
        }

        return redirect()->route('password.reset.form')
                         ->with('status', 'OTP verified! Please set your new password.');
    }
}
