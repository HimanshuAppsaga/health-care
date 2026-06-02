<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'patient' => [
                'name' => $this->name ?? $this->patient?->name,
                'phone' => $this->phone ?? $this->patient?->phone,
            ],
            'doctor' => [
                'id' => $this->doctor_id,
                'name' => $this->doctor?->user?->name,
            ],
            'appointment_date' => $this->appointment_date,
            'status' => $this->status,
            'clinic_id' => $this->clinic_id,
        ];
    }
}
