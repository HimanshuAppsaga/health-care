<?php

namespace App\Http\Requests\Api;

use App\Http\Resources\ClinicResource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
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
     * Get the cached clinic details and log the access.
     */
    public function getCachedClinicDetails(): array
    {
        $clinic = $this->clinic;

        // Log clinic access
        Log::info('API Access: Clinic Details retrieved', ['clinic_id' => $clinic->id]);

        // Cache the resolved clinic data (array) for 1 hour
        return Cache::remember("clinic_details_data_{$clinic->id}", 3600, function () use ($clinic) {
            return (new ClinicResource($clinic))->resolve();
        });
    }
}
