<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TutorWorkDetail;
use App\Models\TutorWorkDetailApproval;
use App\Models\Notification;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TutorWorkDetailController extends Controller
{
    /**
     * Return a single work detail (scoped to authenticated tutor)
     */
    public function show($id)
    {
        $tutor = Auth::guard('tutor')->user();
        if (! $tutor) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $detail = TutorWorkDetail::where('id', $id)
            ->where('tutor_id', $tutor->tutorID)
            ->first();

        if (! $detail) {
            return response()->json(['message' => 'Work detail not found'], 404);
        }

        return response()->json(['data' => $detail]);
    }

    /**
     * Update a work detail (scoped to authenticated tutor)
     */
    public function update(Request $request, $id)
    {
        $tutor = Auth::guard('tutor')->user();
        if (! $tutor) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $detail = TutorWorkDetail::where('id', $id)
            ->where('tutor_id', $tutor->tutorID)
            ->first();

        if (! $detail) {
            return response()->json(['message' => 'Work detail not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date',
            'ph_time' => 'nullable|string|max:100',
            'class_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Handle optional image upload on update
        if ($request->hasFile('image')) {
            try {
                $filename = time().'_'.$request->file('image')->getClientOriginalName();
                $imagePath = $request->file('image')->storeAs('tutor_work_screenshots', $filename, 'public');
                $detail->screenshot = $imagePath;
            } catch (\Exception $e) {
                Log::warning('Failed to store tutor work image on update: ' . $e->getMessage());
            }
        }

        // Apply editable fields
        $detail->fill($request->only(['date', 'ph_time', 'class_no', 'notes']));

        // If this work detail was previously rejected, mark it as pending again when the tutor updates (resubmission).
        if (is_string($detail->status) && strtolower($detail->status) === 'reject') {
            $oldStatus = $detail->status;
            $detail->status = 'pending';
            $detail->save();

            // Record a resubmission approval record (no supervisor) for auditability
            try {
                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => null,
                    'old_status' => $oldStatus,
                    'new_status' => 'pending',
                    'approved_at' => now(),
                    'note' => 'Resubmitted by tutor',
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to record resubmission approval: ' . $e->getMessage());
            }
        } else {
            $detail->save();
        }

        return response()->json(['message' => 'Updated', 'data' => $detail]);
    }

    /**
     * Delete a work detail (scoped to authenticated tutor)
     */
    public function destroy($id)
    {
        $tutor = Auth::guard('tutor')->user();
        if (! $tutor) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $detail = TutorWorkDetail::where('id', $id)
            ->where('tutor_id', $tutor->tutorID)
            ->first();

        if (! $detail) {
            return response()->json(['message' => 'Work detail not found'], 404);
        }

        $detail->delete();

        return response()->json(['message' => 'Deleted']);
    }
    /**
 * Store a new work detail (scoped to authenticated tutor)
 */
public function store(Request $request)
{
    $tutor = Auth::guard('tutor')->user();
    if (! $tutor) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $validator = Validator::make($request->all(), [
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
        'notes' => 'nullable|string|max:2000',
        'status' => 'nullable|string|in:pending,active,cancelled',
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $imagePath = null;

    if ($request->hasFile('image')){
        $filename = time().'_'.$request->file('image')->getClientOriginalName();
        $imagePath = $request->file('image')->storeAs('tutor_work_screenshots', $filename, 'public');
        $data['screenshot'] =  $imagePath;
    }

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    $detail = TutorWorkDetail::create([
        'tutor_id' => $tutor->tutorID,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'note' => $request->notes,
        'status' => $request->status ?? 'pending',
        'work_type' => 'per class',
        'screenshot' => $imagePath,
    ]);

    // Create one shared notification for all supervisors of the same account
    try {
        $tutorName = $tutor->applicant 
            ? $tutor->applicant->first_name . ' ' . $tutor->applicant->last_name 
            : $tutor->username ?? 'Unknown';
        
        $accountId = $tutor->account_id;
        $supervisorCount = Supervisor::where('status', 'active')
            ->where('assigned_account', $accountId)
            ->count();
        
        // Create one notification shared among supervisors of this account
        Notification::create([
            'type' => 'work_detail_submitted',
            'title' => 'New Work Detail Submitted',
            'message' => "{$tutorName} has submitted new work details for approval.",
            'icon' => 'fas fa-clock',
            'color' => 'blue',
            'is_read' => false,
            'data' => [
                'tutor_id' => $tutor->tutorID,
                'work_detail_id' => $detail->id,
                'work_type' => 'per class',
                'account_id' => $accountId,
                'supervisor_count' => $supervisorCount
            ]
        ]);
        
        Log::info('Work detail notification created for account supervisors', [
            'work_detail_id' => $detail->id,
            'tutor_id' => $tutor->tutorID,
            'account_id' => $accountId,
            'supervisor_count' => $supervisorCount
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to create work detail notification from tutor: ' . $e->getMessage());
    }

    return response()->json(['message' => 'Work detail created', 'data' => $detail]);
}

}
