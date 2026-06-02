<?php

namespace Database\Factories;

use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Factories\Factory;

class QueueFactory extends Factory
{
    protected $model = Queue::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'token_number' => fake()->numberBetween(1, 100),
            'status' => QueueStatus::WAITING,
            'called_at' => null,
        ];
    }
}
