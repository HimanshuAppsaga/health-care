<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayAppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'patient_name' => $this->name ?? $this->patient?->name,
            'appointment_date' => $this->appointment_date,
            'token_no' => $this->token,
            'status' => $this->status->value ?? $this->status,
            'phone' => $this->phone,
        ];
    }
}
