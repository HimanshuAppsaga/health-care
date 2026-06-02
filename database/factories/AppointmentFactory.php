<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'appointment_date' => fake()->date(),
            'status' => AppointmentStatus::PENDING,
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'token' => fake()->numerify('T-####'),
        ];
    }
}
