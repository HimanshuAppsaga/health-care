<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'patient_id',
        'appointment_date',
        'status',
        'name',
        'phone',
        'token',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
