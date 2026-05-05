<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Http\Resources\Api\AppointmentResource;
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
        $clinic = $request->clinic;

        $data = $request->validated();
        $data['clinic_id'] = $clinic->id;

        $result = $this->bookingService->bookAppointment($data, auth('sanctum')->user());

        if (! $result['success']) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], 422);
        }

        return (new AppointmentResource($result))
            ->response()
            ->setStatusCode(201);
    }
}
