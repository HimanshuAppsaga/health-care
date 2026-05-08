<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
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
            'working_hours' => $this->formatWorkingHours($this->working_hours),
        ];
    }

    /**
     * Format working hours to include AM/PM.
     */
    private function formatWorkingHours(?array $workingHours): ?array
    {
        if (! $workingHours) {
            return null;
        }

        $formatted = [];
        foreach ($workingHours as $day => $slots) {
            if (is_array($slots)) {
                $formatted[$day] = array_map(function ($slot) {
                    return [
                        'start_time' => isset($slot['start_time']) ? Carbon::parse($slot['start_time'])->format('h:i A') : null,
                        'end_time' => isset($slot['end_time']) ? Carbon::parse($slot['end_time'])->format('h:i A') : null,
                    ];
                }, $slots);
            } elseif (is_string($slots) && $slots !== 'Closed' && str_contains($slots, ' - ')) {
                $parts = explode(' - ', $slots);
                $formatted[$day] = [
                    [
                        'start_time' => isset($parts[0]) ? Carbon::parse(trim($parts[0]))->format('h:i A') : null,
                        'end_time' => isset($parts[1]) ? Carbon::parse(trim($parts[1]))->format('h:i A') : null,
                    ],
                ];
            } else {
                $formatted[$day] = $slots;
            }
        }

        return $formatted;
    }
}
