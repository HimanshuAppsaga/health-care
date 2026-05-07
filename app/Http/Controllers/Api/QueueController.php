<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LiveQueueRequest;
use App\Http\Resources\QueueResource;
use App\Services\ApiService;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use App\Services\TokenTransferService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    protected $currentTokenService;

    protected $callNextTokenService;

    protected $tokenTransferService;

    public function __construct(
        CurrentTokenService $currentTokenService,
        CallNextTokenService $callNextTokenService,
        TokenTransferService $tokenTransferService
    ) {
        $this->currentTokenService = $currentTokenService;
        $this->callNextTokenService = $callNextTokenService;
        $this->tokenTransferService = $tokenTransferService;
    }

    /**
     * GET /api/queue/live
     * Returns full live queue data for a clinic/doctor
     */
    /**
     * GET /api/queue/live
     * Returns full live queue data for a clinic/doctor
     */
    public function live(LiveQueueRequest $request)
    {
        $data = $request->getLiveQueueData($this->currentTokenService);

        return ApiService::respond('queue', $data, 'Live queue retrieved successfully');
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
            return ApiService::respond('queue', new QueueResource($result['data']['next_token']), $result['message']);
        }

        return ApiService::error($result['message'], $result['message'] === 'No patients in waiting queue' ? 404 : 500);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'transfer_count' => 'required|integer|min:1',
        ]);

        $clinic = $request->clinic;
        $doctorId = $request->doctor_id;
        $transferCount = $request->transfer_count;

        $result = $this->tokenTransferService->transferToken($clinic->id, $doctorId, $transferCount);

        if ($result['success']) {
            return ApiService::respond('transfer', $result['data'] ?? null, $result['message']);
        }

        return ApiService::error($result['message'], 400);
    }
}
