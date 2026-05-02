<?php

namespace App\Livewire\Doctor;

use App\Models\Clinic;
use Illuminate\Support\Str;
use Livewire\Component;

class ClinicSettings extends Component
{
    public ?Clinic $clinic = null;

    public ?string $apiKey = null;

    public ?int $transferDepth = 6;

    protected $rules = [
        'transferDepth' => 'required|integer|min:1|max:9',
    ];

    public function mount()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if ($doctor) {
            $this->clinic = $doctor->clinic;
            $this->apiKey = $this->clinic->api_key;
            $this->transferDepth = $this->clinic->transfer_depth ?? 6;
        } else {
            $this->clinic = Clinic::first();
            $this->apiKey = $this->clinic?->api_key;
            $this->transferDepth = $this->clinic?->transfer_depth ?? 6;
        }
    }

    public function updatedTransferDepth($value)
    {
        $this->validateOnly('transferDepth');

        if ($this->clinic) {
            $this->clinic->update(['transfer_depth' => $value]);
            session()->flash('message', 'Transfer depth updated successfully.');
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
