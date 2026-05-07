<?php

namespace App\Http\Requests\Api;

use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class IndexDoctorRequest extends FormRequest
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
        return [];
    }

    /**
     * Fetch doctors with user relationship and log clinic access.
     */
    public function getDoctors(): LengthAwarePaginator
    {
        $clinic = $this->clinic;

        // Log clinic access
        Log::info('API Access: Doctor list retrieved', ['clinic_id' => $clinic->id]);

        // Fetch doctors with user relationship to avoid N+1
        return Doctor::with('user')
            ->where('clinic_id', $clinic->id)
            ->paginate(10);
    }
}
