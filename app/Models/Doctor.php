<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_id',
        'specialization',
        'qualification',
        'experience_years',
        'consultation_fee',
        'is_on_hold',
        'working_hours',
        'about_me',
        'profile_picture',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    protected function casts(): array
    {
        return [
            'is_on_hold' => 'boolean',
            'consultation_fee' => 'double',
            'working_hours' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActiveDoctor($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'doctor');
            });
        });
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get normalized schedule for a given day (0-6).
     */
    public function getScheduleForDay(int $dayOfWeek): array
    {
        $dayNames = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];

        $dayName = $dayNames[$dayOfWeek];
        $hours = $this->working_hours[$dayName] ?? $this->working_hours[$dayOfWeek] ?? 'Closed';

        if ($hours === 'Closed') {
            return [];
        }

        if (is_string($hours)) {
            $sessions = [];
            $ranges = explode(',', $hours);
            foreach ($ranges as $range) {
                $range = trim($range);
                if (empty($range)) {
                    continue;
                }
                $parts = explode(' - ', $range);
                if (count($parts) === 2) {
                    $endTimeStr = Carbon::parse($parts[1])->format('H:i:s');
                    if ($endTimeStr === '00:00:00') {
                        $endTimeStr = '23:59:59';
                    }
                    $sessions[] = [
                        'start_time' => Carbon::parse($parts[0])->format('H:i:s'),
                        'end_time' => $endTimeStr,
                    ];
                }
            }

            return $sessions;
        }

        return (array) $hours;
    }
}
