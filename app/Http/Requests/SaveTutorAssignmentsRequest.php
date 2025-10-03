<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTutorAssignmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:daily_data,id',
            'assignments' => 'required|array',
            'assignments.*.tutor_id' => 'required|exists:tutors,tutorID',
            'assignments.*.is_backup' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Class ID is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'assignments.required' => 'Assignments are required.',
            'assignments.array' => 'Assignments must be an array.',
            'assignments.*.tutor_id.required' => 'Tutor ID is required for each assignment.',
            'assignments.*.tutor_id.exists' => 'The selected tutor does not exist.',
            'assignments.*.is_backup.boolean' => 'Backup status must be true or false.'
        ];
    }
}
