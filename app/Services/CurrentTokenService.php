<?php

namespace App\Services;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrentTokenService
{
    /**
     * Get the current serving token for a clinic/doctor.
     *
     * @param  int|string  $clinicId
     * @param  int|string|null  $doctorId
     */
    public function getCurrentToken($clinicId, $doctorId = null): array
    {
        try {
            $today = Carbon::today();

            $query = Queue::with(['appointment.doctor.user', 'appointment.clinic'])
                ->whereHas('appointment', function ($q) use ($clinicId, $doctorId, $today) {
                    if ($clinicId) {
                        $q->where('clinic_id', $clinicId);
                    }
                    $q->whereDate('appointment_date', $today);

                    if ($doctorId) {
                        $q->where('doctor_id', $doctorId);
                    }
                });

            $nowServing = (clone $query)->whereIn('status', ['serving', 'hold'])->first();

            return [
                'success' => true,
                'message' => 'Current token fetched successfully',
                'data' => [
                    'current_token' => $nowServing,
                    // Keeping it flexible for other existing fields if needed later
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch current token: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to fetch current token.',
                'data' => [
                    'current_token' => null,
                ],
            ];
        }
    }
}
