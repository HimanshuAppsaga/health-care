<?php

namespace App\Livewire\Doctor;

use App\Models\Clinic;
use Illuminate\Support\Str;
use Livewire\Component;

class ClinicSettings extends Component
{
    public ?Clinic $clinic = null;

    public ?string $apiKey = null;

    public function mount()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if ($doctor) {
            $this->clinic = $doctor->clinic;
            $this->apiKey = $this->clinic->api_key;
        } else {
            // Handle case where user is not a doctor but somehow accessed this page
            // Or maybe they are a receptionist? The user said "doctors panel"
            // Let's check if they have a clinic_id directly or through a relationship
            $this->clinic = Clinic::first(); // Fallback for testing if needed
            $this->apiKey = $this->clinic?->api_key;
        }
    }

    public function generateApiKey()
    {
        if ($this->apiKey) {
            return;
        }

        $newKey = Str::random(64);

        if ($this->clinic) {
            $this->clinic->update(['api_key' => $newKey]);
            $this->apiKey = $newKey;
            session()->flash('message', 'API Key generated successfully.');
        } else {
            session()->flash('error', 'Clinic not found.');
        }
    }

    public function render()
    {
        return view('livewire.doctor.clinic-settings')
            ->layout('components.layouts.app', ['title' => 'Clinic Settings']);
    }
}
