<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AppointmentHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'date_range' => ['nullable', 'string', 'in:all,today,week,month,custom'],
            'start_date' => ['nullable', 'date', 'required_if:date_range,custom'],
            'end_date' => ['nullable', 'date', 'required_if:date_range,custom'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'patient_id' => ['nullable', 'exists:patients,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
