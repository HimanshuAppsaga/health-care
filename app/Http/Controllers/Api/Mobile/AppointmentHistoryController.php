<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AppointmentHistoryRequest;
use App\Http\Resources\Api\Mobile\AppointmentHistoryResource;
use App\Services\ApiService;
use App\Services\AppointmentService;
use Exception;
use Illuminate\Http\JsonResponse;

class AppointmentHistoryController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Display a listing of the appointment history.
     */
    public function index(AppointmentHistoryRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();

            // Inject clinic_id from middleware context
            $filters['clinic_id'] = $request->get('clinic_id');

            // Fetch appointments using the shared service logic
            // true flag enables pagination as requested
            $appointments = $this->appointmentService->getAppointments($filters, true);

            return ApiService::respond(
                'appointments',
                [
                    'list' => AppointmentHistoryResource::collection($appointments),
                    'pagination' => [
                        'total' => $appointments->total(),
                        'per_page' => $appointments->perPage(),
                        'current_page' => $appointments->currentPage(),
                        'last_page' => $appointments->lastPage(),
                    ],
                ],
                'Appointment history retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiService::error('Failed to retrieve appointment history: '.$e->getMessage(), 500);
        }
    }
}
