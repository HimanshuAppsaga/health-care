<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TodayAppointmentRequest;
use App\Http\Resources\TodayAppointmentResource;
use App\Services\ApiService;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;

class TodayAppointmentController extends Controller
{
    /**
     * Display a listing of today's appointments.
     */
    public function index(TodayAppointmentRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        $filters = $request->validated();

        // Add clinic_id from middleware context
        $filters['clinic_id'] = $request->get('clinic_id');

        $appointments = $appointmentService->getTodayAppointments($filters);

        return ApiService::respond(
            'appointments',
            TodayAppointmentResource::collection($appointments),
            'Today appointments fetched successfully'
        );
    }
}
