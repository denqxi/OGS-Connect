<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TutorAvailabilityController extends Controller
{
    /**
     * Get current availability for the authenticated tutor
     */
    public function getAvailability()
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            // Load applicant relationships for tutor details
            $tutor->load(['applicant.qualification', 'applicant.requirement']);

            // Get all accounts for this tutor
            $accounts = $tutor->accounts()->get();
            
            $availability = [];
            foreach ($accounts as $account) {
                // Ensure available_days is an array
                $availableDays = $account->available_days;
                if (is_string($availableDays)) {
                    $availableDays = json_decode($availableDays, true) ?? [];
                }
                if (!is_array($availableDays)) {
                    $availableDays = [];
                }

                // Sort available_days in chronological order
                $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $availableDays = array_values(array_intersect($dayOrder, $availableDays));

                // Ensure available_times is an object/array
                $availableTimes = $account->available_times;
                if (is_string($availableTimes)) {
                    $availableTimes = json_decode($availableTimes, true) ?? [];
                }
                if (!is_array($availableTimes)) {
                    $availableTimes = [];
                }

                // Sort available_times keys in chronological order
                $sortedAvailableTimes = [];
                foreach ($dayOrder as $day) {
                    if (isset($availableTimes[$day])) {
                        $sortedAvailableTimes[$day] = $availableTimes[$day];
                    }
                }
                $availableTimes = $sortedAvailableTimes;

                // Normalize time format and limit to one time slot per day for display
                foreach ($availableTimes as $day => $timeSlots) {
                    if (is_array($timeSlots) && !empty($timeSlots)) {
                        // Take only the first time slot for each day
                        $firstTimeSlot = $timeSlots[0];
                        // Convert "20:00-21:00" to "20:00 - 21:00"
                        $normalizedTimeSlot = str_replace('-', ' - ', $firstTimeSlot);
                        $availableTimes[$day] = [$normalizedTimeSlot];
                    }
                }

                $availability[$account->account_name] = [
                    'id' => $account->id,
                    'account_name' => $account->account_name,
                    'available_days' => $availableDays,
                    'available_times' => $availableTimes,
                    'timezone' => $account->timezone,
                    'notes' => $account->notes,
                    'operating_start_time' => $account->account?->operating_start_time,
                    'operating_end_time' => $account->account?->operating_end_time,
                    'company_rules' => $account->account?->company_rules,
                ];
            }

            // Get tutor details, payment information, and security questions for additional information
            $tutorDetails = $tutor->tutorDetails;
            // TODO: Uncomment when employee_payment_information table exists
            // $paymentMethods = $tutor->paymentMethods;
            // $securityQuestions = $tutor->securityQuestions()->take(2)->get();
            
            return response()->json([
                'success' => true,
                'data' => $availability,
                'tutor_info' => [
                    'full_name' => $tutor->full_name,
                    'first_name' => $tutor->first_name,
                    'last_name' => $tutor->last_name,
                    'email' => $tutor->email,
                    'tutorID' => $tutor->tutorID,
                    'date_of_birth' => $tutor->date_of_birth ?? null,
                    'address' => $tutorDetails->address ?? null,
                    'contact_number' => $tutor->phone_number ?? null,
                    'ms_teams_id' => $tutorDetails->ms_teams_id ?? null,
                    // TODO: Uncomment when employee_payment_information table exists
                    // 'payment_info' => $paymentMethods->map(function($payment) {
                    //     return [
                    //         'id' => $payment->id,
                    //         'payment_method' => $payment->payment_method ?? null,
                    //         'payment_method_uppercase' => $payment->payment_method_uppercase ?? null,
                    //         'bank_name' => $payment->bank_name ?? null,
                    //         'account_number' => $payment->account_number ?? null,
                    //         'account_name' => $payment->account_name ?? null,
                    //         'paypal_email' => $payment->paypal_email ?? null,
                    //         'gcash_number' => $payment->gcash_number ?? null,
                    //         'paymaya_number' => $payment->paymaya_number ?? null,
                    //         'created_at' => $payment->created_at,
                    //         'updated_at' => $payment->updated_at
                    //     ];
                    // })->toArray(),
                    'payment_info' => [], // Temporary empty array
                    // TODO: Uncomment when security_questions relationship is fixed
                    // 'security_questions' => $securityQuestions->map(function($question) {
                    //     return [
                    //         'question' => $question->question ?? null,
                    //         'answer' => '***' // Don't expose the actual answer for security
                    //     ];
                    // })->toArray()
                    'security_questions' => [] // Temporary empty array
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting tutor availability: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve availability data'
            ], 500);
        }
    }

    /**
     * Update availability for a specific account
     */
    public function updateAvailability(Request $request)
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'account_name' => 'required|string|max:255',
                'available_days' => 'required|array',
                'available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'available_times' => 'required|array',
                'timezone' => 'nullable|string|max:10',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $accountName = $request->input('account_name');
            $availableDays = $request->input('available_days');
            $availableTimes = $request->input('available_times');

            // Validate time format and restrictions for each day
            foreach ($availableTimes as $day => $times) {
                if (!is_array($times)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid time format for {$day}"
                    ], 422);
                }

                foreach ($times as $timeRange) {
                    if (!preg_match('/^\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2}$/', $timeRange)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid time range format: {$timeRange}. Expected format: HH:MM - HH:MM"
                        ], 422);
                    }

                    // Validate against account-specific time restrictions
                    $timeValidation = $this->validateTimeRestrictions($accountName, $timeRange);
                    if (!$timeValidation['valid']) {
                        return response()->json([
                            'success' => false,
                            'message' => $timeValidation['message']
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            // Find or create the tutor account
            $tutorAccount = $tutor->accounts()
                ->where('account_name', $accountName)
                ->first();

            if (!$tutorAccount) {
                // Create new account if it doesn't exist
                $tutorAccount = new TutorAccount([
                    'tutor_id' => $tutor->tutorID,
                    'account_name' => $accountName,
                    'status' => 'active',
                ]);
            }

            // Update availability data
            $tutorAccount->available_days = $availableDays;
            $tutorAccount->available_times = $availableTimes;
            $tutorAccount->timezone = $request->input('timezone', 'UTC');
            $tutorAccount->notes = $request->input('notes');

            $tutorAccount->save();

            DB::commit();

            Log::info('Tutor availability updated', [
                'tutor_id' => $tutor->tutorID,
                'account_name' => $accountName,
                'available_days' => $availableDays,
                'available_times' => $availableTimes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully',
                'data' => [
                    'account_name' => $accountName,
                    'available_days' => $availableDays,
                    'available_times' => $availableTimes
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating tutor availability: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability'
            ], 500);
        }
    }

    /**
     * Update multiple accounts at once
     */
    public function updateMultipleAccounts(Request $request)
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            $accountsData = $request->input('accounts', []);

            if (empty($accountsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No accounts data provided'
                ], 422);
            }

            DB::beginTransaction();

            $updatedAccounts = [];

            foreach ($accountsData as $accountData) {
                $validator = Validator::make($accountData, [
                    'account_name' => 'required|string|max:255',
                    'available_days' => 'required|array',
                    'available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                    'available_times' => 'required|array',
                    'timezone' => 'nullable|string|max:10',
                    'notes' => 'nullable|string|max:1000',
                ]);

                if ($validator->fails()) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Validation failed for account: {$accountData['account_name']}",
                        'errors' => $validator->errors()
                    ], 422);
                }

                $accountName = $accountData['account_name'];
                $availableDays = $accountData['available_days'];
                $availableTimes = $accountData['available_times'];

                // Sort available_days in chronological order
                $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $availableDays = array_values(array_intersect($dayOrder, $availableDays));

                // Sort available_times keys in chronological order
                $sortedAvailableTimes = [];
                foreach ($dayOrder as $day) {
                    if (isset($availableTimes[$day])) {
                        $sortedAvailableTimes[$day] = $availableTimes[$day];
                    }
                }
                $availableTimes = $sortedAvailableTimes;

                // Validate time restrictions for this account
                foreach ($availableTimes as $day => $times) {
                    if (is_array($times)) {
                        foreach ($times as $timeRange) {
                            $timeValidation = $this->validateTimeRestrictions($accountName, $timeRange);
                            if (!$timeValidation['valid']) {
                                DB::rollback();
                                return response()->json([
                                    'success' => false,
                                    'message' => $timeValidation['message']
                                ], 422);
                            }
                        }
                    }
                }

                // Find or create the tutor account
                $tutorAccount = $tutor->accounts()
                    ->where('account_name', $accountName)
                    ->first();

                if (!$tutorAccount) {
                    // Get account_id from accounts table
                    $account = \App\Models\Account::where('account_name', $accountName)->first();
                    if (!$account) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "Account {$accountName} not found in accounts table"
                        ], 404);
                    }

                    $tutorAccount = new TutorAccount([
                        'tutor_id' => $tutor->tutorID,
                        'account_id' => $account->account_id,
                        'account_name' => $accountName,
                    ]);
                }

                // Update availability data
                $tutorAccount->available_days = $availableDays;
                $tutorAccount->available_times = $availableTimes;
                $tutorAccount->timezone = $accountData['timezone'] ?? 'UTC';
                $tutorAccount->notes = $accountData['notes'] ?? null;

                $tutorAccount->save();

                $updatedAccounts[] = $accountName;
            }

            DB::commit();

            Log::info('Multiple tutor accounts updated', [
                'tutor_id' => $tutor->tutorID,
                'updated_accounts' => $updatedAccounts
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All accounts updated successfully',
                'updated_accounts' => $updatedAccounts
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating multiple tutor accounts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update accounts'
            ], 500);
        }
    }

    /**
     * Get time slots for a specific day
     */
    public function getTimeSlots(Request $request)
    {
        try {
            $accountName = $request->input('account_name');
            $day = $request->input('day');

            if (!$accountName || !$day) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account name and day are required'
                ], 422);
            }

            $tutor = Auth::guard('tutor')->user();
            $account = $tutor->accounts()->whereHas('account', function($q) use ($accountName) {
                $q->where('account_name', $accountName);
            })->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found'
                ], 404);
            }

            $availableTimes = $account->available_times;
            $dayTimes = $availableTimes[$day] ?? [];

            return response()->json([
                'success' => true,
                'data' => [
                    'day' => $day,
                    'time_slots' => $dayTimes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting time slots: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve time slots'
            ], 500);
        }
    }

    /**
     * Validate time restrictions for specific accounts
     */
    private function validateTimeRestrictions($accountName, $timeRange)
    {
        $timeRestrictions = [
            'GLS' => [
                'start' => '07:00',
                'end' => '15:30',
                'enabled' => true
            ],
            'Babilala' => [
                'start' => '20:00',
                'end' => '22:00',
                'enabled' => true
            ],
            'Tutlo' => [
                'start' => '00:00',
                'end' => '23:30',
                'enabled' => false
            ],
            'Talk915' => [
                'start' => '00:00',
                'end' => '23:30',
                'enabled' => false
            ]
        ];

        // If no restrictions, allow any time
        if (!isset($timeRestrictions[$accountName]) || !$timeRestrictions[$accountName]['enabled']) {
            return ['valid' => true];
        }

        $restriction = $timeRestrictions[$accountName];
        [$startTime, $endTime] = explode(' - ', $timeRange);

        // Convert times to minutes for comparison
        $startMinutes = $this->timeToMinutes($startTime);
        $endMinutes = $this->timeToMinutes($endTime);
        $restrictionStartMinutes = $this->timeToMinutes($restriction['start']);
        $restrictionEndMinutes = $this->timeToMinutes($restriction['end']);

        // Check if time range is within restrictions
        if ($startMinutes < $restrictionStartMinutes || $endMinutes > $restrictionEndMinutes) {
            return [
                'valid' => false,
                'message' => "Time range {$timeRange} is outside allowed hours for {$accountName} ({$restriction['start']} - {$restriction['end']})"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Setup payment information for the authenticated tutor
     */
    public function setupPayment(Request $request)
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|string|in:gcash,paypal,paymaya,bank_transfer,cash',
                'account_name' => 'required|string|max:255',
                'gcash_number' => 'nullable|string|max:20',
                'paypal_email' => 'nullable|email|max:255',
                'paymaya_number' => 'nullable|string|max:20',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $paymentData = $request->only([
                'payment_method',
                'account_name',
                'gcash_number',
                'paypal_email',
                'paymaya_number',
                'bank_name',
                'account_number'
            ]);

            // Add payment method uppercase for display
            $paymentData['payment_method_uppercase'] = strtoupper(str_replace('_', ' ', $paymentData['payment_method']));
            
            // Add employee type and ID for the relationship
            $paymentData['employee_id'] = $tutor->tutorID;
            $paymentData['employee_type'] = 'tutor';

            // Check if tutor already has payment information
            $existingPayment = $tutor->paymentMethods()->first();
            
            if ($existingPayment) {
                // Update existing payment information with new method
                $existingPayment->update($paymentData);
                $message = 'Payment information updated successfully';
            } else {
                // Create new payment information
                $tutor->paymentMethods()->create($paymentData);
                $message = 'Payment information added successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Error setting up payment information: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while setting up payment information. Please try again.'
            ], 500);
        }
    }

    /**
     * Update a specific payment method
     */
    public function updatePaymentMethod(Request $request, $paymentId)
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            // Find the payment method
            $paymentMethod = $tutor->paymentMethods()->find($paymentId);
            
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|string|in:gcash,paypal,paymaya,bank_transfer,cash',
                'account_name' => 'required|string|max:255',
                'gcash_number' => 'nullable|string|max:20',
                'paypal_email' => 'nullable|email|max:255',
                'paymaya_number' => 'nullable|string|max:20',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $paymentData = $request->only([
                'payment_method',
                'account_name',
                'gcash_number',
                'paypal_email',
                'paymaya_number',
                'bank_name',
                'account_number'
            ]);

            // Add payment method uppercase for display
            $paymentData['payment_method_uppercase'] = strtoupper(str_replace('_', ' ', $paymentData['payment_method']));

            // Update the payment method
            $paymentMethod->update($paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the payment method. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a specific payment method
     */
    public function deletePaymentMethod($paymentId)
    {
        try {
            $tutor = Auth::guard('tutor')->user();
            
            if (!$tutor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutor not authenticated'
                ], 401);
            }

            // Find the payment method
            $paymentMethod = $tutor->paymentMethods()->find($paymentId);
            
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found'
                ], 404);
            }

            // Delete the payment method
            $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error deleting payment method: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while deleting the payment method. Please try again.'
        ], 500);
    }
}

/**
 * Update tutor's personal information
 */
public function updatePersonalInfo(Request $request)
{
    try {
        $tutor = Auth::guard('tutor')->user();
        
        if (!$tutor) {
            return response()->json([
                'success' => false,
                'message' => 'Tutor not authenticated'
            ], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'ms_teams_id' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update tutor information
        $tutor->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);

        // Update applicant details (tutor details are stored in applicant table)
        $applicant = $tutor->applicant;
        if ($applicant) {
            $applicant->update([
                'address' => $request->address,
                'ms_teams' => $request->ms_teams_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Personal information updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating personal information: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating your personal information. Please try again.'
        ], 500);
    }
}

/**
 * Change tutor's password
 */
public function changePassword(Request $request)
{
    try {
        $tutor = Auth::guard('tutor')->user();
        
        if (!$tutor) {
            return response()->json([
                'success' => false,
                'message' => 'Tutor not authenticated'
            ], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $tutor->tpassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $tutor->update([
            'tpassword' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error changing password: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while changing your password. Please try again.'
        ], 500);
    }
}

/**
 * Update tutor's security questions
 */
public function updateSecurityQuestions(Request $request)
{
    try {
        $tutor = Auth::guard('tutor')->user();
        
        if (!$tutor) {
            return response()->json([
                'success' => false,
                'message' => 'Tutor not authenticated'
            ], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|string|max:500',
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete existing security questions
        $tutor->securityQuestions()->delete();

        // Create new security questions
        foreach ($request->questions as $index => $question) {
            if (isset($request->answers[$index])) {
                $tutor->securityQuestions()->create([
                    'user_type' => 'tutor',
                    'user_id' => $tutor->tutorID,
                    'question' => $question,
                    'answer_hash' => Hash::make(strtolower(trim($request->answers[$index]))),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Security questions updated successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating security questions: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating your security questions. Please try again.'
        ], 500);
    }
}

    /**
     * Convert time string to minutes
     */
    private function timeToMinutes($time)
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }
}
