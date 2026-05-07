<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CallNextTokenRequest;
use App\Http\Requests\Api\LiveQueueRequest;
use App\Http\Requests\Api\TransferTokenRequest;
use App\Http\Resources\QueueResource;
use App\Services\ApiService;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use App\Services\TokenTransferService;

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
     * Returns current serving patient for a clinic/doctor
     */
    public function live(LiveQueueRequest $request)
    {
        $data = $request->getCurrentPatientData($this->currentTokenService);

        return ApiService::respond('queue', $data, 'Current patient retrieved successfully');
    }

    /**
     * GET /api/queue/waiting-list
     * Returns waiting list for a clinic/doctor
     */
    public function waitingList(LiveQueueRequest $request)
    {
        $data = $request->getWaitingListData();

        return ApiService::respond('queue', $data, 'Waiting list retrieved successfully');
    }

    /**
     * POST /api/queue/call-next
     * Move next patient to "in-progress" (serving)
     */
    public function callNext(CallNextTokenRequest $request)
    {
        $result = $this->callNextTokenService->callNextToken($request->clinic->id, $request->doctor_id);

        if ($result['success']) {
            return ApiService::respond('queue', new QueueResource($result['data']['next_token']), $result['message']);
        }

        return ApiService::error($result['message'], $result['message'] === 'No patients in waiting queue' ? 404 : 500);
    }

    public function transfer(TransferTokenRequest $request)
    {
        $clinic = $request->clinic;
        $doctorId = $request->doctor_id;
        $transferCount = $clinic->transfer_depth ?: 1;

        $result = $this->tokenTransferService->transferToken($clinic->id, $doctorId, $transferCount);

        if ($result['success']) {
            return ApiService::respond('transfer', $result['data'] ?? null, $result['message']);
        }

        return ApiService::error($result['message'], 400);
    }
}
