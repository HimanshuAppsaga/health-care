<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'specialization' => fake()->jobTitle(),
            'qualification' => 'MBBS, MD',
            'experience_years' => fake()->numberBetween(1, 30),
            'consultation_fee' => fake()->randomFloat(2, 50, 500),
            'is_on_hold' => false,
            'working_hours' => [
                'monday' => '09:00 - 13:00, 14:00 - 17:00',
                'tuesday' => '09:00 - 13:00, 14:00 - 17:00',
                'wednesday' => '09:00 - 13:00, 14:00 - 17:00',
                'thursday' => '09:00 - 13:00, 14:00 - 17:00',
                'friday' => '09:00 - 13:00, 14:00 - 17:00',
            ],
        ];
    }
}
