<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Resources\Api\AppointmentResource;
use App\Services\ApiService;
use App\Services\AppointmentBookingService;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentBookingService $bookingService
    ) {}

    /**
     * Store a newly created appointment.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['clinic_id'] = $request->clinic_id;

        $result = $this->bookingService->bookAppointment($data, auth('sanctum')->user());

        if (! $result['success']) {
            return ApiService::error($result['message'], 422);
        }

        return ApiService::respond(
            'appointment',
            new AppointmentResource($result['data']['appointment']),
            $result['message'],
            201
        );
    }
}
