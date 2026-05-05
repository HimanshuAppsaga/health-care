<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;

class ClinicEdit extends Component
{
    use WithFileUploads;

    public $clinic;

    public $name;
    public $description;
    public $address;
    public $contact_number;
    public $about_clinic;
    public $latitude;
    public $longitude;
    public $working_hours = [];

    public $logo;
    public $removeLogo = false;

    public function mount($id)
    {
        $this->clinic = Clinic::findOrFail($id);

        $this->name = $this->clinic->name;
        $this->description = $this->clinic->description;
        $this->address = $this->clinic->address;
        $this->contact_number = $this->clinic->contact_number;
        $this->about_clinic = $this->clinic->about_clinic;
        $this->latitude = $this->clinic->latitude;
        $this->longitude = $this->clinic->longitude;
        $this->working_hours = $this->clinic->working_hours ?? [];
    }

    public function removeExistingLogo()
    {
        $this->removeLogo = true;
    }

    public function cancelNewLogo()
    {
        $this->logo = null;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        // REMOVE LOGO
        if ($this->removeLogo && $this->clinic->logo) {
            if (Storage::disk('public')->exists($this->clinic->logo)) {
                Storage::disk('public')->delete($this->clinic->logo);
            }
            $this->clinic->logo = null;
        }

        // NEW LOGO UPLOAD
        if ($this->logo) {
            if ($this->clinic->logo && Storage::disk('public')->exists($this->clinic->logo)) {
                Storage::disk('public')->delete($this->clinic->logo);
            }

            $this->clinic->logo = $this->logo->store('clinic-logos', 'public');
        }

        $this->clinic->update([
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'contact_number' => $this->contact_number,
            'about_clinic' => $this->about_clinic,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'working_hours' => $this->working_hours,
            'logo' => $this->clinic->logo,
        ]);

        // RESET STATES
        $this->logo = null;
        $this->removeLogo = false;

        session()->flash('success', 'Clinic updated successfully!');
    }

    public function render()
    {
        return view('livewire.doctor.clinic-edit')
            ->layout('layouts.app');
    }
}