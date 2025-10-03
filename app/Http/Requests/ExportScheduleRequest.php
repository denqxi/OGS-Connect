<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportScheduleRequest extends FormRequest
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
            'date' => 'required|date',
            'schedule_ids' => 'sometimes|array',
            'schedule_ids.*' => 'exists:daily_data,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Date is required for export.',
            'date.date' => 'Date must be a valid date.',
            'schedule_ids.array' => 'Schedule IDs must be an array.',
            'schedule_ids.*.exists' => 'One or more selected schedules do not exist.'
        ];
    }
}
