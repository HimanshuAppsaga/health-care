<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Mark an appointment as done.
     *
     * @param  int|string  $appointmentId
     * @param  int|string  $clinicId
     *
     * @throws \Exception
     */
    public function markAsDone($appointmentId, $clinicId): bool
    {
        return DB::transaction(function () use ($appointmentId, $clinicId) {
            $appointment = Appointment::where('id', $appointmentId)
                ->where('clinic_id', $clinicId)
                ->first();

            if (! $appointment) {
                return false;
            }

            // Find the queue record for this appointment
            $queue = Queue::where('appointment_id', $appointmentId)->first();

            if (! $queue) {
                return false;
            }

            // Update statuses
            $queue->update(['status' => QueueStatus::COMPLETED]);
            $appointment->update(['status' => AppointmentStatus::COMPLETED]);

            // If the doctor was on hold, maybe we should continue?
            // In web app, it doesn't explicitly toggle doctor hold off when marking as done.
            // But usually, once done, the doctor is ready for next.

            broadcast(new QueueUpdated($clinicId, 'completed'))->toOthers();

            return true;
        });
    }

    /**
     * Put an appointment on hold.
     *
     * @param  int|string  $appointmentId
     * @param  int|string  $clinicId
     *
     * @throws \Exception
     */
    public function hold($appointmentId, $clinicId): bool
    {
        return DB::transaction(function () use ($appointmentId, $clinicId) {
            $appointment = Appointment::where('id', $appointmentId)
                ->where('clinic_id', $clinicId)
                ->first();

            if (! $appointment) {
                return false;
            }

            $queue = Queue::where('appointment_id', $appointmentId)->first();

            if (! $queue) {
                return false;
            }

            // Change status to hold
            $queue->update(['status' => QueueStatus::HOLD]);

            // In web app, holding also toggles doctor's is_on_hold if they were serving.
            // Let's check if this is the doctor's current patient.
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor && ! $doctor->is_on_hold) {
                $doctor->update(['is_on_hold' => true]);
            }

            broadcast(new QueueUpdated($clinicId, 'hold'))->toOthers();

            return true;
        });
    }
}
