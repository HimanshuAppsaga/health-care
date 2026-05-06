<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Http\Resources\Api\AppointmentResource;
use App\Http\Requests\Api\CheckTokenRequest;

class TokenController extends Controller
{
   public function checkToken(CheckTokenRequest $request)
    {
        $clinicId = $request->clinic_id;

        if (!$clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'Clinic ID missing from middleware',
                'data' => null
            ], 400);
        }

        $appointment = Appointment::where('clinic_id', $clinicId)
            ->where('phone', trim($request->phone))
            ->latest()
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No token booked for this user',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token found',
            'data' => new AppointmentResource($appointment)
        ]);
    }
}