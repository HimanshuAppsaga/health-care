<?php

namespace App\Http\Requests\Api;

use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'doctor_id' => [
                'required',
                'exists:doctors,id',
                // Security: Ensure the doctor belongs to this clinic
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Doctor::where('id', $value)->where('clinic_id', $this->clinic?->id)->exists()) {
                        $fail('The selected doctor does not belong to your clinic.');
                    }
                },
            ],
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'date' => 'nullable|date',
        ];
    }
}
