<?php

namespace App\Services;

use App\Events\QueueUpdated;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CallNextTokenService
{
    /**
     * Call the next token in the queue for a specific clinic and doctor.
     *
     * @param  int|string  $clinicId
     * @param  int|string|null  $doctorId
     */
    public function callNextToken($clinicId, $doctorId = null): array
    {
        try {
            $today = Carbon::today();
            $clinic = Clinic::find($clinicId);
            $clinicName = $clinic ? $clinic->name : 'Unknown Clinic';

            if ($doctorId) {
                $doctor = Doctor::find($doctorId);
                if ($doctor && $doctor->is_on_hold) {
                    return [
                        'success' => false,
                        'message' => 'The doctor is currently on hold and cannot call the next patient.',
                    ];
                }
            }

            // 1. Check if there is already a patient being served or on hold
            $current = Queue::whereHas('appointment', function ($q) use ($clinicId, $doctorId, $today) {
                if ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                }
                $q->whereDate('appointment_date', $today);

                if ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                }
            })->whereIn('status', ['serving', 'hold'])->first();

            if ($current) {
                return [
                    'success' => false,
                    'message' => 'A patient is already being served. Please complete the current patient before calling the next one.',
                ];
            }

            // 2. Find next waiting patient
            $next = Queue::whereHas('appointment', function ($q) use ($clinicId, $doctorId, $today) {
                if ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                }
                $q->whereDate('appointment_date', $today);

                if ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                }
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
                broadcast(new QueueUpdated($clinicId, 'next'))->toOthers();

                Log::info("Clinic [{$clinicName}] ID:{$clinicId} - Token #{$next->token_number} is NOW SERVING.");

                return [
                    'success' => true,
                    'message' => 'Next patient called successfully',
                    'data' => [
                        'current_token' => $current,
                        'next_token' => $next,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'No patients in waiting queue',
                'data' => [
                    'current_token' => $current,
                    'next_token' => null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('CallNextTokenService Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while calling the next patient.',
                'data' => [
                    'current_token' => null,
                    'next_token' => null,
                ],
            ];
        }
    }
}
