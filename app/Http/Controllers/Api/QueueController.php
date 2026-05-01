<?php

namespace App\Http\Controllers\Api;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\QueueResource;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
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

        $nowServing = (clone $query)->whereIn('status', ['serving', 'hold'])->first();

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
        $today = Carbon::today();

        // 1. Complete current serving patient if any
        $current = Queue::whereHas('appointment', function ($q) use ($clinic, $doctorId, $today) {
            $q->where('clinic_id', $clinic->id)
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $current->update(['status' => 'completed']);
            if ($current->appointment) {
                $current->appointment->update(['status' => 'completed']);
            }
            Log::info("Clinic [{$clinic->name}] ID:{$clinic->id} - Token #{$current->token_number} marked COMPLETED.");
        }

        // 2. Find next waiting patient
        $next = Queue::whereHas('appointment', function ($q) use ($clinic, $doctorId, $today) {
            $q->where('clinic_id', $clinic->id)
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->first();

        if ($next) {
            $next->update([
                'status' => 'serving',
                'called_at' => now(),
            ]);

            // Broadcast for real-time updates
            broadcast(new QueueUpdated($clinic->id, 'next'))->toOthers();

            Log::info("Clinic [{$clinic->name}] ID:{$clinic->id} - Token #{$next->token_number} is NOW SERVING.");

            return response()->json([
                'status' => true,
                'message' => 'Next patient called successfully',
                'data' => new QueueResource($next),
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No patients in waiting queue',
        ], 404);
    }
}
