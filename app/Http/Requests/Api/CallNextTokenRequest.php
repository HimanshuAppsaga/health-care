<?php

namespace App\Http\Requests\Api;

use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CallNextTokenRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $doctor = Doctor::find($value);
                    if ($doctor && $doctor->is_on_hold) {
                        $fail('The doctor is currently on hold and cannot call the next patient.');
                    }
                },
            ],
        ];
    }
}
