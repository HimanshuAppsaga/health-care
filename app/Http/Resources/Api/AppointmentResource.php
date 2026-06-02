<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'token' => $this->token,
            'clinic_id' => $this->clinic_id,
            'doctor_id' => $this->doctor_id,
            'appointment_date' => $this->appointment_date,
            'status' => $this->status,
        ];
    }
}
