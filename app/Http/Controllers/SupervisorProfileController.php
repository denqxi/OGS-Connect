<?php

namespace App\Http\Controllers;

use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SupervisorProfileController extends Controller
{
    /**
     * Display the supervisor profile page
     */
    public function index()
    {
        $supervisor = Auth::user();
        
        // Load security questions
        $securityQuestions = $supervisor->securityQuestions;
        
        // Extract questions for the view
        $securityQuestion1 = $securityQuestions->get(0)?->question ?? null;
        $securityQuestion2 = $securityQuestions->get(1)?->question ?? null;
        
        return view('profile_management.supervisor', compact('supervisor', 'securityQuestion1', 'securityQuestion2'));
    }

    /**
     * Update security questions
     */
    public function updateSecurityQuestions(Request $request)
    {
        $supervisor = Auth::user();

        $request->validate([
            'security_question1' => 'required|string|max:255',
            'security_answer1' => 'required|string|min:2',
            'security_question2' => 'required|string|max:255',
            'security_answer2' => 'required|string|min:2',
        ], [
            'security_question1.required' => 'First security question is required.',
            'security_answer1.required' => 'Answer to first security question is required.',
            'security_answer1.min' => 'Answer must be at least 2 characters.',
            'security_question2.required' => 'Second security question is required.',
            'security_answer2.required' => 'Answer to second security question is required.',
            'security_answer2.min' => 'Answer must be at least 2 characters.',
        ]);

        try {
            // Delete existing security questions for this supervisor
            SecurityQuestion::where('user_type', 'supervisor')
                           ->where('user_id', $supervisor->supervisor_id)
                           ->delete();

            // Create new security questions
            SecurityQuestion::create([
                'user_type' => 'supervisor',
                'user_id' => $supervisor->supervisor_id,
                'question' => $request->security_question1,
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer1))),
            ]);

            SecurityQuestion::create([
                'user_type' => 'supervisor',
                'user_id' => $supervisor->supervisor_id,
                'question' => $request->security_question2,
                'answer_hash' => Hash::make(strtolower(trim($request->security_answer2))),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Security questions updated successfully!'
                ]);
            }
            
            return redirect()->back()->with('success', 'Security questions updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update security questions. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update security questions. Please try again.');
        }
    }

    /**
     * Update supervisor role - DISABLED (Role is read-only)
     * This method is kept for backward compatibility but should not be used
     */
    public function updateRole(Request $request)
    {
        // Role updates are not allowed - roles are assigned by system administrators
        return redirect()->back()->with('error', 'Role updates are not allowed. Please contact your system administrator.');
    }

    /**
     * Update supervisor personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $supervisor = Auth::user();

        $request->validate([
            'sfname' => 'required|string|max:255',
            'smname' => 'nullable|string|max:255',
            'slname' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'sconNum' => 'nullable|string|max:20',
            'saddress' => 'nullable|string|max:500',
            'steams' => 'nullable|email|max:255',
            'sshift' => 'nullable|string|max:255',
        ], [
            'sfname.required' => 'First name is required.',
            'slname.required' => 'Last name is required.',
            'birth_date.date' => 'Birth date must be a valid date.',
            'sconNum.max' => 'Contact number must not exceed 20 characters.',
            'saddress.max' => 'Address must not exceed 500 characters.',
            'steams.email' => 'MS Teams email must be a valid email address.',
            'sshift.max' => 'Shift must not exceed 255 characters.',
        ]);

        try {
            $supervisor->update([
                'first_name' => $request->sfname,
                'middle_name' => $request->smname,
                'last_name' => $request->slname,
                'birth_date' => $request->birth_date,
                'contact_number' => $request->sconNum,
                'saddress' => $request->saddress,
                'ms_teams' => $request->steams,
                'sshift' => $request->sshift,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Personal information updated successfully!'
                ]);
            }
            
            return redirect()->back()->with('success', 'Personal information updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update personal information. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update personal information. Please try again.');
        }
    }

    /**
     * Update supervisor password
     */
    public function updatePassword(Request $request)
    {
        $supervisor = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password_confirmation.required' => 'Password confirmation is required.',
            'new_password_confirmation.min' => 'Password confirmation must be at least 8 characters.',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $supervisor->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 422);
            }
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        // Check if new password is different from current password
        if (Hash::check($request->new_password, $supervisor->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password.'
                ], 422);
            }
            return redirect()->back()->withErrors(['new_password' => 'New password must be different from current password.'])->withInput();
        }

        try {
            $supervisor->update([
                'password' => Hash::make($request->new_password),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully!'
                ]);
            }
            
            return redirect()->back()->with('success', 'Password updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update password. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Update payment information
     */
    public function updatePaymentInfo(Request $request)
    {
        $supervisor = Auth::user();

        $request->validate([
            'payment_method' => 'required|string|in:gcash,paypal,paymaya,bank_transfer,cash',
            'gcash_number' => 'required_if:payment_method,gcash|nullable|string|max:20',
            'paypal_email' => 'required_if:payment_method,paypal|nullable|email|max:255',
            'paymaya_number' => 'required_if:payment_method,paymaya|nullable|string|max:20',
            'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
            'account_number' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Store payment info in JSON format in supervisor table
            $paymentData = [
                'payment_method' => $request->payment_method,
                'updated_at' => now(),
            ];

            switch ($request->payment_method) {
                case 'gcash':
                    $paymentData['gcash_number'] = $request->gcash_number;
                    break;
                case 'paypal':
                    $paymentData['paypal_email'] = $request->paypal_email;
                    break;
                case 'paymaya':
                    $paymentData['paymaya_number'] = $request->paymaya_number;
                    break;
                case 'bank_transfer':
                    $paymentData['bank_name'] = $request->bank_name;
                    $paymentData['account_number'] = $request->account_number;
                    $paymentData['account_name'] = $request->account_name;
                    break;
            }

            if ($request->notes) {
                $paymentData['notes'] = $request->notes;
            }

            $supervisor->update([
                'payment_info' => json_encode($paymentData)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment information updated successfully!',
                    'payment_info' => $paymentData
                ]);
            }
            
            return redirect()->back()->with('success', 'Payment information updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Payment info update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment information. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update payment information. Please try again.');
        }
    }

    /**
     * Update profile photo
     */
    public function updateProfilePhoto(Request $request)
    {
        $supervisor = Auth::user();

        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'profile_photo.required' => 'Please select a photo to upload.',
            'profile_photo.image' => 'File must be an image.',
            'profile_photo.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif.',
            'profile_photo.max' => 'Image must not exceed 2MB.',
        ]);

        try {
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($supervisor->profile_photo && \Storage::disk('public')->exists($supervisor->profile_photo)) {
                    \Storage::disk('public')->delete($supervisor->profile_photo);
                }

                // Store new photo
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                
                $supervisor->update([
                    'profile_photo' => $path
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Profile photo updated successfully!',
                        'photo_url' => asset('storage/' . $path)
                    ]);
                }
                
                return redirect()->back()->with('success', 'Profile photo updated successfully!');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No photo file received.'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'No photo file received.');

        } catch (\Exception $e) {
            \Log::error('Profile photo update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile photo. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update profile photo. Please try again.');
        }
    }
}