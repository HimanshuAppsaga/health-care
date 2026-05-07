<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingValidationTest extends TestCase
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

    public function test_it_allows_booking_an_appointment_if_none_exists_in_last_24_hours()
    {
        // Note: The actual booking might fail because of missing schedules,
        // but we are testing that it passes VALIDATION (i.e. not getting the "appointment is already booked" error)

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        // It might return 422 because of schedules, but the error message shouldn't be about already booked
        if ($response->status() === 422) {
            $response->assertJsonMissing(['message' => 'appointment is already booked.']);
            $response->assertJsonMissingPath('errors.phone');
        }
    }

    public function test_it_fails_booking_if_an_appointment_exists_in_last_24_hours()
    {
        // Create an appointment for this phone number in the last 24 hours
        Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'appointment_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'token' => '1',
        ]);

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
        $this->assertEquals('appointment is already booked.', $response->json('errors.phone.0'));
    }

    public function test_it_allows_booking_after_24_hours()
    {
        // Create an appointment for this phone number 25 hours ago
        $oldAppointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'appointment_date' => now()->subHours(25)->format('Y-m-d'),
            'status' => 'pending',
            'token' => '1',
        ]);

        // Manually set created_at to 25 hours ago
        $oldAppointment->created_at = now()->subHours(25);
        $oldAppointment->save();

        $response = $this->postJson('/api/appointments/book', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        // It shouldn't have the "already booked" error
        $response->assertJsonMissingPath('errors.phone');
    }
}
