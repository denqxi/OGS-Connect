<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorAccount;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
            $tutor->load(['applicant.qualification', 'applicant.requirement', 'applicant.workPreference', 'account', 'securityQuestions']);

            // Get work preferences for this tutor's applicant
            $workPreference = $tutor->applicant?->workPreference;
            
            $availability = [];
            if ($workPreference) {
                // Get account name from tutor's assigned account
                $accountName = $tutor->account?->account_name ?? 'Default Account';
                
                // Ensure available_days is an array (column is called days_available)
                $availableDays = $workPreference->days_available ?? [];
                if (is_string($availableDays)) {
                    $availableDays = json_decode($availableDays, true) ?? [];
                }
                if (!is_array($availableDays)) {
                    $availableDays = [];
                }

                // Sort available_days in chronological order
                $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $availableDays = array_values(array_intersect($dayOrder, $availableDays));

                // Build available times from work_preference start/end times
                $availableTimes = [];
                if ($workPreference->start_time && $workPreference->end_time) {
                    $timeSlot = $workPreference->start_time . ' - ' . $workPreference->end_time;
                    foreach ($availableDays as $day) {
                        $availableTimes[$day] = [$timeSlot];
                    }
                }

                $availability[$accountName] = [
                    'id' => $workPreference->id,
                    'account_name' => $accountName,
                    'available_days' => $availableDays,
                    'available_times' => $availableTimes,
                    'timezone' => $workPreference->timezone ?? 'UTC',
                    'notes' => $workPreference->notes ?? '',
                    'operating_start_time' => $tutor->account?->operating_start_time,
                    'operating_end_time' => $tutor->account?->operating_end_time,
                    'company_rules' => $tutor->account?->company_rules,
                ];
            }

            // Get tutor details
            $tutorDetails = $tutor->tutorDetails;
            
            // Load payment methods
            $paymentMethods = $tutor->paymentMethods;
            
            return response()->json([
                'success' => true,
                'data' => $availability,
                'tutor_info' => [
                    'full_name' => $tutor->full_name,
                    'first_name' => $tutor->first_name,
                    'last_name' => $tutor->last_name,
                    'email' => $tutor->email,
                    'tutorID' => $tutor->tutorID,
                    'account_name' => $tutor->account?->account_name ?? null,
                    'date_of_birth' => $tutor->date_of_birth ?? null,
                    'address' => $tutorDetails->address ?? null,
                    'contact_number' => $tutor->phone_number ?? null,
                    'ms_teams_id' => $tutorDetails->ms_teams_id ?? null,
                    'payment_info' => $paymentMethods->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'payment_method' => $payment->payment_method ?? null,
                            'payment_method_uppercase' => $payment->payment_method_uppercase ?? null,
                            'bank_name' => $payment->bank_name ?? null,
                            'account_number' => $payment->account_number ?? null,
                            'account_name' => $payment->account_name ?? null,
                            'paypal_email' => $payment->paypal_email ?? null,
                            'gcash_number' => $payment->gcash_number ?? null,
                            'paymaya_number' => $payment->paymaya_number ?? null,
                            'created_at' => $payment->created_at,
                            'updated_at' => $payment->updated_at
                        ];
                    })->toArray(),
                    'security_questions' => $tutor->securityQuestions->map(function($question) {
                        return [
                            'question' => $question->question ?? null,
                            'answer' => '***' // Don't expose the actual answer for security
                        ];
                    })->toArray()
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

            // Since tutor_accounts was dropped and consolidated to work_preferences,
            // update the applicant's work_preference record
            $applicant = $tutor->applicant;
            if (!$applicant) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Applicant not found for this tutor'
                ], 404);
            }

            // Process the first account data (since we only have one work_preference now)
            $accountData = $accountsData[0] ?? null;
            
            if (!$accountData) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'No account data provided'
                ], 422);
            }

            $validator = Validator::make($accountData, [
                'account_name' => 'required|string|max:255',
                // Allow tutors to clear availability; default to empty array when not provided
                'available_days' => 'nullable|array',
                'available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'available_times' => 'nullable|array',
                // Timezone strings like "Asia/Manila" exceed 10 chars; be permissive
                'timezone' => 'nullable|string|max:50',
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

            $availableDays = $accountData['available_days'] ?? [];
            $availableTimes = $accountData['available_times'] ?? [];

            // Sort available_days in chronological order (handle empty arrays)
            if (!empty($availableDays)) {
                $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $availableDays = array_values(array_intersect($dayOrder, $availableDays));
            }

            // Extract start and end time from available_times (first time slot)
            // Default to full day availability (00:00 - 23:59) if no times specified
            $startTime = '00:00:00';
            $endTime = '23:59:59';
            
            if (!empty($availableTimes)) {
                // Get the first available time slot from the first day
                foreach ($dayOrder as $day) {
                    if (isset($availableTimes[$day]) && is_array($availableTimes[$day]) && !empty($availableTimes[$day])) {
                        $timeRange = $availableTimes[$day][0];
                        // Parse "HH:MM - HH:MM" format
                        $times = explode(' - ', $timeRange);
                        if (count($times) === 2) {
                            $startTime = trim($times[0]);
                            $endTime = trim($times[1]);
                        }
                        break;
                    }
                }
            }

            // Get or create work preference for this applicant
            $workPreference = $applicant->workPreference;
            
            if (!$workPreference) {
                $workPreference = new \App\Models\WorkPreference([
                    'applicant_id' => $applicant->applicant_id,
                    'days_available' => json_encode($availableDays),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'platform' => json_encode(['Online']), // Default platform
                    'can_teach' => json_encode([]), // Default empty
                ]);
            } else {
                $workPreference->days_available = json_encode($availableDays);
                $workPreference->start_time = $startTime;
                $workPreference->end_time = $endTime;
            }

            $workPreference->save();

            DB::commit();

            Log::info('Tutor availability updated', [
                'tutor_id' => $tutor->tutorID,
                'applicant_id' => $applicant->applicant_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully'
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

        $applicantId = $tutor->applicant?->applicant_id;

        // Build validation rules dynamically - only validate email uniqueness if email changed
        $emailRules = [
            'required',
            'email',
            'max:255',
        ];
        
        // Only check uniqueness if email is being changed
        if ($tutor->applicant && $tutor->applicant->email !== $request->email) {
            $emailRules[] = Rule::unique('applicants', 'email')->ignore($applicantId, 'applicant_id');
            $emailRules[] = Rule::unique('tutors', 'email')->ignore($tutor->tutor_id, 'tutor_id');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:500',
            'email' => $emailRules,
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

        // Normalize date to Y-m-d for DB
        $normalizedBirthDate = null;
        if ($request->filled('date_of_birth')) {
            try {
                $normalizedBirthDate = Carbon::parse($request->date_of_birth)->format('Y-m-d');
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date of birth format.',
                ], 422);
            }
        }

        // Update applicant details (source of truth for personal info)
        $applicant = $tutor->applicant;
        $changedFields = [];

        if ($applicant) {
            // Track what changed in applicant
            if ($applicant->first_name !== $request->first_name) {
                $changedFields['First Name'] = "{$applicant->first_name} → {$request->first_name}";
            }
            if (($applicant->middle_name ?? null) !== ($request->middle_name ?? null)) {
                $changedFields['Middle Name'] = ($applicant->middle_name ?? 'empty') . " → " . ($request->middle_name ?? 'empty');
            }
            if ($applicant->last_name !== $request->last_name) {
                $changedFields['Last Name'] = "{$applicant->last_name} → {$request->last_name}";
            }
            if (($applicant->birth_date ?? null) !== $normalizedBirthDate) {
                $changedFields['Date of Birth'] = ($applicant->birth_date ?? 'N/A') . " → {$normalizedBirthDate}";
            }
            if (($applicant->address ?? null) !== $request->address) {
                $changedFields['Address'] = ($applicant->address ?? 'empty') . " → {$request->address}";
            }
            if (($applicant->contact_number ?? null) !== $request->phone_number) {
                $changedFields['Contact Number'] = ($applicant->contact_number ?? 'empty') . " → {$request->phone_number}";
            }
            if ($applicant->email !== $request->email) {
                $changedFields['Email'] = "{$applicant->email} → {$request->email}";
            }
            if (($applicant->ms_teams ?? null) !== $request->ms_teams_id) {
                $changedFields['MS Teams ID'] = ($applicant->ms_teams ?? 'empty') . " → {$request->ms_teams_id}";
            }

            $applicant->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'birth_date' => $normalizedBirthDate,
                'address' => $request->address,
                'contact_number' => $request->phone_number,
                'email' => $request->email,
                'ms_teams' => $request->ms_teams_id,
            ]);
        }

        // Keep tutor auth email in sync
        $tutor->update([
            'email' => $request->email,
        ]);

        // Build summary message
        $message = 'Personal information updated successfully';
        if (!empty($changedFields)) {
            $message .= '. Updated fields: ' . implode(', ', array_keys($changedFields));
        } else {
            $message = 'No changes were made. All fields have the same values.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'changed_fields' => $changedFields,
            'fields_count' => count($changedFields)
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
 * Update tutor's profile photo
 */
public function updateProfilePhoto(Request $request)
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
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete old profile photo if exists
        if ($tutor->profile_photo && \Storage::disk('public')->exists($tutor->profile_photo)) {
            \Storage::disk('public')->delete($tutor->profile_photo);
        }

        // Store new profile photo
        $path = $request->file('profile_photo')->store('profile_photos', 'public');

        // Update tutor's profile photo
        $tutor->update([
            'profile_photo' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully',
            'photo_url' => asset('storage/' . $path)
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating profile photo: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating your profile photo. Please try again.'
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
        SecurityQuestion::where('user_type', 'tutor')
                       ->where('user_id', $tutor->tutor_id)
                       ->delete();

        // Create new security questions
        foreach ($request->questions as $index => $question) {
            if (isset($request->answers[$index])) {
                SecurityQuestion::create([
                    'user_type' => 'tutor',
                    'user_id' => $tutor->tutor_id,
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
