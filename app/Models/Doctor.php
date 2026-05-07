<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }
}
