<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QueueResource;
use App\Models\Queue;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    protected $currentTokenService;

    protected $callNextTokenService;

    public function __construct(CurrentTokenService $currentTokenService, CallNextTokenService $callNextTokenService)
    {
        $this->currentTokenService = $currentTokenService;
        $this->callNextTokenService = $callNextTokenService;
    }

    /**
     * GET /api/queue/live
     * Returns full live queue data for a clinic/doctor
     */
    public function live(Request $request)
    {
        $clinic = $request->clinic;
        $doctorId = $request->input('doctor_id');
        $today = Carbon::today();

        $query = Queue::with(['appointment.doctor.user'])
            ->whereHas('appointment', function ($q) use ($clinic, $doctorId, $today) {
                $q->where('clinic_id', $clinic->id)
                    ->whereDate('appointment_date', $today);

                if ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                }
            });

        $result = $this->currentTokenService->getCurrentToken($clinic->id, $doctorId);
        $nowServing = $result['data']['current_token'];

        $waitingList = (clone $query)->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'data' => [
                'current_patient' => $nowServing ? new QueueResource($nowServing) : null,
                'waiting_list' => QueueResource::collection($waitingList)->response()->getData(true),
            ],
            'meta' => [
                'clinic_name' => $clinic->name,
                'date' => $today->toDateString(),
            ],
        ]);
    }

    /**
     * POST /api/queue/call-next
     * Move next patient to "in-progress" (serving)
     */
    public function callNext(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $clinic = $request->clinic;
        $doctorId = $request->doctor_id;

        $result = $this->callNextTokenService->callNextToken($clinic->id, $doctorId);

        if ($result['success']) {
            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'data' => new QueueResource($result['data']['next_token']),
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message'],
        ], $result['message'] === 'No patients in waiting queue' ? 404 : 500);
    }
}
