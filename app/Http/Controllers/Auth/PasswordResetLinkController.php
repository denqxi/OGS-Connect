<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Supervisor;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create()
    {
        // Redirect to login page since password reset is integrated there
        return redirect()->route('login')->with('show_reset', true);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        Log::info('Password reset request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'data' => $request->all(),
            'expects_json' => $request->expectsJson(),
            'headers' => $request->headers->all()
        ]);

        try {
            Log::info('Starting password reset process');
        
        try {
            $request->validate([
                'email' => 'required|string|max:255',
                'user_type' => 'required|in:tutor,supervisor,auto',
            ], [
                'email.required' => 'Email is required.',
                'user_type.required' => 'Please select your account type.',
                'user_type.in' => 'Invalid account type selected.',
            ]);
            
            Log::info('Validation passed');
        } catch (\Exception $e) {
            Log::error('Validation failed', ['error' => $e->getMessage()]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $e->getMessage()
                ], 422);
            }
            throw $e;
        }

        $email = $request->email;
        $userType = $request->user_type;
        
        Log::info('Looking for user', ['email' => $email, 'user_type' => $userType]);

        // Find the user based on type and username
        $user = null;
        
        // If email is provided or user_type is 'auto', check both tutors and supervisors
        if (filter_var($email, FILTER_VALIDATE_EMAIL) || $userType === 'auto') {
            Log::info('Email or auto-detect mode, searching in both tutor and supervisor tables');
            
            // Clean the email input
            $cleanEmail = trim(strtolower($email));
            Log::info('Cleaned email for search', ['original' => $email, 'cleaned' => $cleanEmail]);
            
            // First try tutors with comprehensive whitespace removal
            $user = Tutor::whereRaw('LOWER(TRIM(REPLACE(REPLACE(email, CHAR(10), ""), CHAR(13), ""))) = ?', [$cleanEmail])->first();
            
            if ($user) {
                $userType = 'tutor';
                Log::info('User found in tutors table', ['user_id' => $user->tutorID]);
            } else {
                Log::info('User not found in tutors table, checking supervisors');
                
                // Try supervisors with comprehensive whitespace removal (removes \n and \r)
                $user = Supervisor::whereRaw('LOWER(TRIM(REPLACE(REPLACE(semail, CHAR(10), ""), CHAR(13), ""))) = ?', [$cleanEmail])->first();
                Log::info('Supervisor search with comprehensive TRIM', ['found' => !!$user]);
                
                if ($user) {
                    $userType = 'supervisor';
                    Log::info('User found in supervisors table', ['user_id' => $user->supID]);
                } else {
                    Log::info('User not found in supervisors table either');
                    
                    // Additional debug: try a LIKE search to see if there's any partial match
                    $partialMatch = Supervisor::whereRaw('semail LIKE ?', ["%$cleanEmail%"])->first();
                    Log::info('Partial match search', ['found' => !!$partialMatch]);
                    if ($partialMatch) {
                        Log::info('Partial match details', [
                            'id' => $partialMatch->supID,
                            'raw_email' => bin2hex($partialMatch->semail),
                            'email_length' => strlen($partialMatch->semail)
                        ]);
                    }
                }
            }
        } else {
            // Non-email input, treat as username and use the specified user type
            if ($userType === 'tutor') {
                $user = Tutor::where('tusername', $email)
                            ->orWhere('tutorID', $email)
                            ->first();
            } elseif ($userType === 'supervisor') {
                $user = Supervisor::where('susername', $email)
                                 ->orWhere('supID', $email)
                                 ->first();
            }
        }
        
        Log::info('User search result', ['user_found' => !!$user, 'user_id' => $user ? $user->id : null, 'final_user_type' => $userType]);

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with the provided email address.'
                ], 404);
            }
            return back()->withErrors([
                'email' => 'No account found with the provided email address.',
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email address found for this account. Please contact support.'
                ], 400);
            }
            return back()->withErrors([
                'email' => 'No email address found for this account. Please contact support.',
            ])->withInput();
        }

        // Clean the email (remove any whitespace/newlines)
        $cleanUserEmail = trim($email);
        Log::info('Attempting to send password reset link', ['email' => $cleanUserEmail, 'user_type' => $userType]);
        
        // For custom user tables, we need to use a different approach
        // Create a password reset token manually
        $token = \Illuminate\Support\Str::random(60);
        
        // Store the token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $cleanUserEmail],
            [
                'email' => $cleanUserEmail,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        
        // Send the email notification
        try {
            $resetUrl = url('/reset-password/' . $token) . '?email=' . urlencode($cleanUserEmail);
            
            // Send password reset email directly
            Mail::send('emails.password-reset-simple', [
                'resetUrl' => $resetUrl,
                'userType' => $userType,
                'user' => $user
            ], function ($message) use ($cleanUserEmail) {
                $message->to($cleanUserEmail)
                        ->subject('Password Reset Request - OGS Connect')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            Log::info('Password reset email sent successfully', [
                'to' => $cleanUserEmail,
                'reset_url' => $resetUrl,
                'user_type' => $userType,
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent to your email address.',
                    'debug_reset_url' => $resetUrl // For testing purposes
                ]);
            }
            
            return back()->with('status', 'Password reset link sent to your email address.');
            
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send password reset email. Please try again.'
                ], 500);
            }
            
            return back()->withErrors(['email' => 'Failed to send password reset email. Please try again.']);
        }
        } catch (\Exception $e) {
            Log::error('Unexpected error in password reset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['username' => 'An unexpected error occurred. Please try again.']);
        }
    }
}
