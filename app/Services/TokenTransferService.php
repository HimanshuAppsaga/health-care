<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenTransferService
{
    /**
     * Calculate the new token number after transfer.
     *
     * NOTE: This is kept for backward compatibility if needed,
     * but the main logic now uses a sequence-based approach.
     */
    public function calculateTransferredToken(int $currentToken, int $transferCount, int $lastToken): int
    {
        if ($transferCount <= 0) {
            return $currentToken;
        }

        $newToken = $currentToken + $transferCount;

        if ($newToken > $lastToken) {
            $newToken = $lastToken;
        }

        return $newToken;
    }

    /**
     * Transfer the currently serving token by a specified count (skipping people).
     *
     * @param  int|string  $clinicId
     * @param  int|string  $doctorId
     */
    public function transferToken($clinicId, $doctorId, int $transferCount): array
    {
        if ($transferCount < 0) {
            return [
                'success' => false,
                'message' => 'Transfer count cannot be negative.',
            ];
        }

        if ($transferCount === 0) {
            return [
                'success' => true,
                'message' => 'Transfer count is zero, no changes made.',
            ];
        }

        return DB::transaction(function () use ($clinicId, $doctorId, $transferCount) {
            $today = Carbon::today();

            // 1. Find the current serving token for this doctor today
            $currentQueue = Queue::with('appointment')
                ->whereHas('appointment', function ($query) use ($doctorId, $today) {
                    $query->where('doctor_id', $doctorId)
                        ->whereDate('appointment_date', $today);
                })
                ->where('status', QueueStatus::SERVING)
                ->lockForUpdate()
                ->first();

            if (! $currentQueue) {
                return [
                    'success' => false,
                    'message' => 'No patient is currently being served.',
                ];
            }

            $currentAppointment = $currentQueue->appointment;

            // 2. Get all waiting appointments for this doctor today, ordered by token
            $waitingAppointments = Appointment::with('queue')
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today)
                ->whereHas('queue', function ($q) {
                    $q->where('status', QueueStatus::WAITING);
                })
                ->orderByRaw('CAST(token AS UNSIGNED) ASC')
                ->lockForUpdate()
                ->get();

            if ($waitingAppointments->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No other patients in queue. Token remains serving or at the end.',
                ];
            }

            // 3. Determine how many people to skip
            $actualSkipCount = min($transferCount, $waitingAppointments->count());

            // Appointments involved in the rotation: [Current, Next1, Next2, ..., NextN]
            $rotationList = collect([$currentAppointment])->concat($waitingAppointments->take($actualSkipCount));
            $originalTokens = $rotationList->pluck('token')->toArray();

            // 4. Perform the rotation
            // A[1] gets T[0], A[2] gets T[1], ..., A[0] gets T[N-1]
            for ($i = 1; $i < $rotationList->count(); $i++) {
                $app = $rotationList[$i];
                $newToken = $originalTokens[$i - 1];
                $this->updateAppointmentAndQueue($app, $newToken, QueueStatus::WAITING);
            }

            // The originally serving patient moves to the last token in the rotation
            $finalToken = $originalTokens[$rotationList->count() - 1];
            $this->updateAppointmentAndQueue($currentAppointment, $finalToken, QueueStatus::WAITING);

            Log::info("Token Transfer (Sequence): Clinic {$clinicId}, Doctor {$doctorId}, Skipped {$actualSkipCount} people.");

            broadcast(new QueueUpdated($clinicId, 'transfer'))->toOthers();

            return [
                'success' => true,
                'message' => "Token successfully transferred behind {$actualSkipCount} patients.",
                'data' => [
                    'skipped_count' => $actualSkipCount,
                    'new_token' => $finalToken,
                ],
            ];
        });
    }

    /**
     * Helper to update both Appointment and Queue records.
     */
    private function updateAppointmentAndQueue(Appointment $appointment, string $token, QueueStatus $status): void
    {
        $appointment->update([
            'token' => $token,
            'status' => AppointmentStatus::PENDING,
        ]);

        if ($appointment->queue) {
            $appointment->queue->update([
                'token_number' => $token,
                'status' => $status,
            ]);
        }
    }
}
