<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token_number' => $this->token_number,
            'status' => $this->status,
            'called_at' => $this->called_at ? $this->called_at->toDateTimeString() : null,
            'appointment' => [
                'id' => $this->appointment->id,
                'patient_name' => $this->appointment->name,
                'patient_phone' => $this->appointment->phone,
                'status' => $this->appointment->status,
            ],
            'doctor' => [
                'id' => $this->appointment->doctor->id,
                'name' => $this->appointment->doctor->user->name,
                'specialization' => $this->appointment->doctor->specialization,
            ],
        ];
    }
}
