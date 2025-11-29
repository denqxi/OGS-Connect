<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TutorWorkDetail;
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
            'status' => 'nullable|string|in:pending,active,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $detail->fill($request->only(['date', 'ph_time', 'class_no', 'notes', 'status']));
        $detail->save();

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
        'date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
        'class_no' => 'nullable|string|max:50',
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
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'class_no' => $request->class_no,
        'notes' => $request->notes,
        'status' => $request->status ?? 'pending',
        'work_type' => 'per class',
        'screenshot' => $imagePath,
    ]);

    return response()->json(['message' => 'Work detail created', 'data' => $detail]);
}

}
