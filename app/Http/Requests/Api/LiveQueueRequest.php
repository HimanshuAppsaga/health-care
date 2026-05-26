<?php

namespace App\Http\Requests\Api;

use App\Http\Resources\QueueResource;
use App\Models\Appointment;
use App\Models\Queue;
use App\Services\CurrentTokenService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LiveQueueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get the resolved doctor ID for the request.
     */
    public function getResolvedDoctorId(): ?int
    {
        if ($this->has('appointment_id')) {
            $appointment = Appointment::find($this->input('appointment_id'));
            if ($appointment) {
                return $appointment->doctor_id;
            }
        }

        return $this->input('doctor_id');
    }

    /**
     * Get the current patient data.
     */
    public function getCurrentPatientData(CurrentTokenService $currentTokenService): array
    {
        $clinic = $this->clinic;
        $doctorId = $this->getResolvedDoctorId();
        $today = Carbon::today();

        $result = $currentTokenService->getCurrentToken($clinic->id, $doctorId);
        $nowServing = $result['data']['current_token'];

        return [
            'current_patient' => $nowServing ? new QueueResource($nowServing) : null,
            'clinic_name' => $clinic->name,
            'date' => $today->toDateString(),
        ];
    }

    /**
     * Get the waiting list data.
     */
    public function getWaitingListData(): array
    {
        $clinic = $this->clinic;
        $doctorId = $this->getResolvedDoctorId();
        $today = Carbon::today();

        $query = Queue::with(['appointment.doctor.user'])
            ->whereHas('appointment', function ($q) use ($clinic, $doctorId, $today) {
                $q->where('clinic_id', $clinic->id)
                    ->whereDate('appointment_date', $today);

                if ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                }
            });

        $waitingList = $query->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->paginate($this->input('per_page', 15));

        return [
            'waiting_list' => QueueResource::collection($waitingList)->response()->getData(true)['data'],
            'clinic_name' => $clinic->name,
            'date' => $today->toDateString(),
        ];
    }
}
