<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Clinic',
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'about_clinic' => fake()->paragraph(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'working_hours' => [
                'monday' => '09:00 - 17:00',
                'tuesday' => '09:00 - 17:00',
                'wednesday' => '09:00 - 17:00',
                'thursday' => '09:00 - 17:00',
                'friday' => '09:00 - 17:00',
            ],
            'logo' => null,
            'api_key' => Clinic::generateUniqueApiKey(),
            'transfer_depth' => fake()->numberBetween(1, 3),
        ];
    }
}
