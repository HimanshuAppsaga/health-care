<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTimeWindowTest extends TestCase
{
    use RefreshDatabase;

    protected $clinic;
    protected $doctor;
    protected $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Dr. Smith',
            'email' => 'smith@example.com',
            'phone' => '0987654321',
            'password' => bcrypt('password'),
        ]);

        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-api-key',
        ]);

        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'specialization' => 'General',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'consultation_fee' => 500,
            'working_hours' => [
                'monday' => '09:00:00 - 10:00:00',
                'tuesday' => '09:00:00 - 10:00:00',
                'wednesday' => '09:00:00 - 10:00:00',
                'thursday' => '09:00:00 - 10:00:00',
                'friday' => '09:00:00 - 10:00:00',
                'saturday' => '09:00:00 - 10:00:00',
                'sunday' => '09:00:00 - 10:00:00',
            ],
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'dob' => '1990-01-01',
            'gender' => 'male',
        ]);
    }

    public function test_it_allows_booking_within_window()
    {
        // Schedule is 09:00 AM - 10:00 AM
        // Window is 08:45 AM - 09:45 AM
        
        // Set time to Monday 08:50 AM (2026-05-11 is a Monday)
        Carbon::setTestNow(Carbon::parse('2026-05-11 08:50:00'));

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'message' => 'Appointment booked successfully!',
            ],
        ]);
    }

    public function test_it_blocks_booking_before_window()
    {
        // Set time to Monday 08:40 AM
        Carbon::setTestNow(Carbon::parse('2026-05-11 08:40:00'));

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'data' => [
                'message' => 'Appointment booking will start at 08:45 AM',
            ],
        ]);
    }

    public function test_it_blocks_booking_after_window()
    {
        // Set time to Monday 09:50 AM
        Carbon::setTestNow(Carbon::parse('2026-05-11 09:50:00'));

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'data' => [
                'message' => "Today's appointment booking time has ended",
            ],
        ]);
    }

    public function test_it_blocks_booking_when_no_schedule_exists()
    {
        // Update doctor to be closed on Monday
        $this->doctor->update([
            'working_hours' => [
                'monday' => 'Closed',
            ],
        ]);

        // Set time to Monday 09:00 AM
        Carbon::setTestNow(Carbon::parse('2026-05-11 09:00:00'));

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'data' => [
                'message' => 'No slot available for today',
            ],
        ]);
    }
}
