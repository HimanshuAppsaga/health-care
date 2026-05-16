<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TodayScheduleRequest;
use App\Http\Resources\TodayScheduleResource;
use App\Services\ApiService;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Get today's schedule for a doctor.
     */
    public function todaySchedule(TodayScheduleRequest $request): JsonResponse
    {
        try {
            $clinicId = $request->get('clinic_id'); // From Middleware
            $doctorId = $request->validated('doctor_id');

            $schedules = $this->scheduleService->getTodaySchedules($doctorId, $clinicId);

            return ApiService::respond(
                'schedules',
                TodayScheduleResource::collection($schedules),
                'Today schedule fetched successfully'
            );
        } catch (\Exception $e) {
            return ApiService::error('Something went wrong: '.$e->getMessage(), 500);
        }
    }
}
