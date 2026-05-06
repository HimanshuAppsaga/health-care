<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckTokenRequest;
use App\Http\Resources\Api\AppointmentResource;
use App\Models\Appointment;
use App\Services\ApiService;

class TokenController extends Controller
{
    public function checkToken(CheckTokenRequest $request)
    {
        $clinicId = $request->clinic_id;

        if (! $clinicId) {
            return ApiService::error('Clinic ID missing from middleware', 400);
        }

        $appointment = Appointment::where('clinic_id', $clinicId)
            ->where('phone', trim($request->phone))
            ->latest()
            ->first();

        if (! $appointment) {
            return ApiService::error('No token booked for this user', 404);
        }

        return ApiService::respond('appointment', new AppointmentResource($appointment), 'Token found');
    }
}
