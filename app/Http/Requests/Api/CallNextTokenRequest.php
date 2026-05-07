<?php

namespace App\Http\Requests\Api;

use App\Models\Doctor;
use App\Models\Queue;
use Carbon\Carbon;
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

                    // Check for current patient
                    $today = Carbon::today();
                    $clinicId = $this->clinic->id;
                    $current = Queue::whereHas('appointment', function ($q) use ($clinicId, $value, $today) {
                        $q->where('clinic_id', $clinicId)
                            ->whereDate('appointment_date', $today)
                            ->where('doctor_id', $value);
                    })->whereIn('status', ['serving', 'hold'])->first();

                    if ($current) {
                        $fail('A patient is already being served. Please complete the current patient before calling the next one.');
                    }
                },
            ],
        ];
    }
}
