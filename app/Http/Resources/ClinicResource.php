<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ClinicResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'about_clinic' => $this->about_clinic,
            'working_hours' => $this->working_hours,
            'contact_number' => $this->contact_number,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo' => $this->logo ? Storage::disk('public')->url($this->logo) : null,

            'address' => $this->address,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
