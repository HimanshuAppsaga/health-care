<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodayScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_schedule_by_doctor_id()
    {
        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-key',
            'working_hours' => [],
        ]);

        $user1 = User::create([
            'name' => 'Doctor 1',
            'email' => 'doc1@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        $doctor1 = Doctor::create([
            'user_id' => $user1->id,
            'clinic_id' => $clinic->id,
            'specialization' => 'General Medicine',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'consultation_fee' => 500,
            'working_hours' => [
                'monday' => '09:00 AM - 05:00 PM',
                'tuesday' => '09:00 AM - 05:00 PM',
                'wednesday' => '09:00 AM - 05:00 PM',
                'thursday' => '09:00 AM - 05:00 PM',
                'friday' => '09:00 AM - 05:00 PM',
                'saturday' => '09:00 AM - 05:00 PM',
                'sunday' => '09:00 AM - 05:00 PM',
            ],
        ]);

        $user2 = User::create([
            'name' => 'Doctor 2',
            'email' => 'doc2@example.com',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
        ]);

        $doctor2 = Doctor::create([
            'user_id' => $user2->id,
            'clinic_id' => $clinic->id,
            'specialization' => 'Pediatrics',
            'qualification' => 'MD',
            'experience_years' => 10,
            'consultation_fee' => 600,
            'working_hours' => [
                'monday' => '09:00 AM - 05:00 PM',
                'tuesday' => '09:00 AM - 05:00 PM',
                'wednesday' => '09:00 AM - 05:00 PM',
                'thursday' => '09:00 AM - 05:00 PM',
                'friday' => '09:00 AM - 05:00 PM',
                'saturday' => '09:00 AM - 05:00 PM',
                'sunday' => '09:00 AM - 05:00 PM',
            ],
        ]);

        // Request with doctor_id=1
        $response = $this->withHeaders(['x-api-key' => 'test-key'])
            ->getJson('/api/today-schedule?doctor_id='.$doctor1->id);

        $response->assertStatus(200);

        // Should only have doctor 1's schedule
        $data = $response->json('data.schedules');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals($doctor1->user->name, $item['doctor_name']);
        }

        // Ensure no schedules from doctor 2 are present
        foreach ($data as $item) {
            $this->assertNotEquals($doctor2->user->name, $item['doctor_name']);
        }
    }

    public function test_it_returns_error_if_no_doctor_id_provided()
    {
        Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-key',
            'working_hours' => [],
        ]);

        $response = $this->withHeaders(['x-api-key' => 'test-key'])
            ->getJson('/api/today-schedule');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['doctor_id']);
    }
}
