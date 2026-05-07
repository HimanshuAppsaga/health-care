<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DoctorResource extends JsonResource
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
            'name' => $this->user->name ?? 'N/A',
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'consultation_fee' => (float) $this->consultation_fee,
            'is_on_hold' => $this->is_on_hold,
            'profile_photo' => ($this->user && $this->user->profile_photo_path)
                ? Storage::disk('public')->url($this->user->profile_photo_path)
                : null,
            'working_hours' => $this->working_hours,

        ];
    }
}
