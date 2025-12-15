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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            ->with(['schedule', 'approvals' => function($q) {
                $q->with('supervisor')->latest('approved_at');
            }])
            ->first();

        if (! $detail) {
            return response()->json(['message' => 'Work detail not found'], 404);
        }

        // Get the latest approval if it exists
        $approval = $detail->approvals->first();
        
        // Format approval response with supervisor details
        $approvalData = null;
        if ($approval) {
            $approvalData = [
                'id' => $approval->id,
                'work_detail_id' => $approval->work_detail_id,
                'supervisor_id' => $approval->supervisor_id,
                'old_status' => $approval->old_status,
                'new_status' => $approval->new_status,
                'approved_at' => $approval->approved_at,
                'note' => $approval->note,
                'supervisor' => $approval->supervisor ? [
                    'supervisor_id' => $approval->supervisor->supervisor_id,
                    'first_name' => $approval->supervisor->first_name,
                    'middle_name' => $approval->supervisor->middle_name,
                    'last_name' => $approval->supervisor->last_name,
                    'full_name' => $approval->supervisor->full_name,
                ] : null
            ];
        }

        return response()->json([
            'success' => true,
            'work_detail' => $detail,
            'approval' => $approvalData
        ]);
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
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Capture old status before any changes
        $oldStatus = $detail->status;

        // Handle optional image upload on update
        if ($request->hasFile('image')) {
            try {
                $filename = time().'_'.$request->file('image')->getClientOriginalName();
                $imagePath = $request->file('image')->storeAs('tutor_work_screenshots', $filename, 'public');
                $detail->proof_image = $imagePath;
            } catch (\Exception $e) {
                Log::warning('Failed to store tutor work image on update: ' . $e->getMessage());
            }
        }

        // Apply editable fields
        $detail->fill($request->only(['date', 'ph_time', 'class_no', 'notes', 'status']));

        // Determine if this is a resubmission (rejected -> pending)
        $newStatus = $detail->status;
        $isResubmission = in_array(strtolower($oldStatus), ['rejected', 'reject']) && strtolower($newStatus) === 'pending';
        
        // Wrap update + approval logging in transaction
        DB::transaction(function () use ($detail, $isResubmission, $oldStatus) {
            $detail->save();

            // If resubmitting (rejected -> pending), record the resubmission
            if ($isResubmission) {
                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => null,
                    'old_status' => $oldStatus,
                    'new_status' => 'pending',
                    'approved_at' => now(),
                    'note' => 'Resubmitted by tutor',
                ]);
            }
        });

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
        'assignment_id' => 'required|integer|exists:assigned_daily_data,id',
        'schedule_daily_data_id' => 'required|integer|exists:schedules_daily_data,id',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i',
        'notes' => 'nullable|string|max:2000',
        'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
    }

    $imagePath = null;

    if ($request->hasFile('image')){
        $filename = time().'_'.$request->file('image')->getClientOriginalName();
        $imagePath = $request->file('image')->storeAs('tutor_work_screenshots', $filename, 'public');
    }

    $date = $request->input('date') ?: now()->toDateString();

    // Compute duration in minutes; allow shifts that cross midnight
    $start = Carbon::createFromFormat('H:i', $request->start_time);
    $end = Carbon::createFromFormat('H:i', $request->end_time);
    if ($end->lessThanOrEqualTo($start)) {
        $end->addDay();
    }
    $duration = abs($end->diffInMinutes($start));

    // Business rule: Tutlo (account_id 2) is hourly @ 120; others per-class @ 50
    $workType = ($tutor->account_id == 2) ? 'hourly' : 'per class';
    $ratePerHour = $workType === 'hourly' ? 120 : 0;
    $ratePerClass = $workType === 'per class' ? 50 : 0;

    $detail = null;

    // Wrap creation in transaction
    DB::transaction(function () use ($tutor, $request, $date, $duration, $workType, $ratePerHour, $ratePerClass, $imagePath, &$detail) {
        $detail = TutorWorkDetail::create([
            'tutor_id' => $tutor->tutorID,
            'assignment_id' => $request->assignment_id,
            'schedule_daily_data_id' => $request->schedule_daily_data_id,
            'date' => $date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $duration,
            'note' => $request->notes,
            'status' => $request->status ?? 'pending',
            'work_type' => $workType,
            'rate_per_hour' => $ratePerHour,
            'rate_per_class' => $ratePerClass,
            'proof_image' => $imagePath,
        ]);
    });

    // After-commit notification to supervisors
    DB::afterCommit(function () use ($tutor, $detail, $workType) {
        if (!$detail) {
            Log::warning('Detail is null in afterCommit, skipping notification');
            return;
        }
        
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
                    'work_type' => $workType,
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
    });

    return response()->json(['message' => 'Work detail created', 'data' => $detail]);
}

}
