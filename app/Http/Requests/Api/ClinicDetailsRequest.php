<?php

namespace App\Http\Requests\Api;

use App\Http\Resources\ClinicResource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ClinicDetailsRequest extends FormRequest
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
     * Get the clinic details and log the access.
     */
    public function getClinicDetails(): array
    {
        $clinic = $this->clinic;

        // Log clinic access
        Log::info('API Access: Clinic Details retrieved', ['clinic_id' => $clinic->id]);

        return (new ClinicResource($clinic))->resolve();
    }
}
