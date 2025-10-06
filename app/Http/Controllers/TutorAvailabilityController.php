<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                    'status' => $account->status,
                    'available_days' => $availableDays,
                    'available_times' => $availableTimes,
                    'preferred_time_range' => $account->preferred_time_range,
                    'timezone' => $account->timezone,
                    'availability_notes' => $account->availability_notes,
                    'restricted_start_time' => $account->restricted_start_time,
                    'restricted_end_time' => $account->restricted_end_time,
                    'company_notes' => $account->company_notes,
                ];
            }

            // Get tutor details, payment information, and security questions for additional information
            $tutorDetails = $tutor->tutorDetails;
            $paymentInfo = $tutor->paymentInformation;
            $securityQuestions = $tutor->securityQuestions()->take(2)->get();
            
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
                    'payment_info' => $paymentInfo ? [
                        'payment_method' => $paymentInfo->payment_method ?? null,
                        'bank_name' => $paymentInfo->bank_name ?? null,
                        'account_number' => $paymentInfo->account_number ?? null,
                        'account_name' => $paymentInfo->account_name ?? null,
                        'paypal_email' => $paymentInfo->paypal_email ?? null,
                        'gcash_number' => $paymentInfo->gcash_number ?? null,
                        'paymaya_number' => $paymentInfo->paymaya_number ?? null
                    ] : null,
                    'security_questions' => $securityQuestions->map(function($question) {
                        return [
                            'question' => $question->question ?? null,
                            'answer' => $question->answer ?? null
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
                'preferred_time_range' => 'nullable|string|in:morning,afternoon,evening,flexible',
                'timezone' => 'nullable|string|max:10',
                'availability_notes' => 'nullable|string|max:1000',
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
            $tutorAccount->preferred_time_range = $request->input('preferred_time_range', 'flexible');
            $tutorAccount->timezone = $request->input('timezone', 'UTC');
            $tutorAccount->availability_notes = $request->input('availability_notes');

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
                    'preferred_time_range' => 'nullable|string|in:morning,afternoon,evening,flexible',
                    'timezone' => 'nullable|string|max:10',
                    'availability_notes' => 'nullable|string|max:1000',
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
                    $tutorAccount = new TutorAccount([
                        'tutor_id' => $tutor->tutorID,
                        'account_name' => $accountName,
                        'status' => 'active',
                    ]);
                }

                // Update availability data
                $tutorAccount->available_days = $availableDays;
                $tutorAccount->available_times = $availableTimes;
                $tutorAccount->preferred_time_range = $accountData['preferred_time_range'] ?? 'flexible';
                $tutorAccount->timezone = $accountData['timezone'] ?? 'UTC';
                $tutorAccount->availability_notes = $accountData['availability_notes'] ?? null;

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
            $account = $tutor->accounts()->where('account_name', $accountName)->first();

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
     * Convert time string to minutes
     */
    private function timeToMinutes($time)
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }
}
