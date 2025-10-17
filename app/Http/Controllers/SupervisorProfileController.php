<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SupervisorProfileController extends Controller
{
    /**
     * Display the supervisor profile page
     */
    public function index()
    {
        $supervisor = Auth::user();
        
        
        // Load payment information relationship
        $supervisor->load(['paymentInformation']);
        
        return view('profile_management.supervisor', compact('supervisor'));
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
                'sfname' => $request->sfname,
                'slname' => $request->slname,
                'birth_date' => $request->birth_date,
                'sconNum' => $request->sconNum,
                'saddress' => $request->saddress,
                'steams' => $request->steams,
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
            'new_password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'new_password_confirmation' => 'required|string',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.mixed_case' => 'New password must contain both uppercase and lowercase letters.',
            'new_password.letters' => 'New password must contain letters.',
            'new_password.numbers' => 'New password must contain numbers.',
            'new_password.symbols' => 'New password must contain symbols.',
            'new_password.uncompromised' => 'New password has appeared in data breaches and cannot be used.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password_confirmation.required' => 'Password confirmation is required.',
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
}