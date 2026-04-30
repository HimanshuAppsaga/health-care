<?php

namespace App\Traits;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;

trait HasClinicScope
{
    /**
     * Scope a query to only include records belonging to the current clinic.
     */
    public function scopeForClinic(Builder $query, int|Clinic $clinic): Builder
    {
        $clinicId = $clinic instanceof Clinic ? $clinic->id : $clinic;
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Get the clinic from the request.
     */
    public function getClinic(): ?Clinic
    {
        return request()->clinic;
    }
}
