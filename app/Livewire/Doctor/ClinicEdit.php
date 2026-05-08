<?php

namespace App\Livewire\Doctor;

use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    public $working_hours_parts = [];

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
        $this->initializeWorkingHoursParts();
    }

    protected function initializeWorkingHoursParts()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $timeString = $this->working_hours[$day] ?? '';

            if ($timeString === 'Closed' || empty($timeString)) {
                $this->working_hours_parts[$day] = [
                    'is_closed' => empty($timeString) ? false : true,
                    'slots' => [
                        [
                            'start_hour' => '09',
                            'start_min' => '00',
                            'start_period' => 'AM',
                            'end_hour' => '05',
                            'end_min' => '00',
                            'end_period' => 'PM',
                        ],
                    ],
                ];
            } else {
                // Parse "09:00 AM - 05:00 PM, 06:00 PM - 09:00 PM"
                $slots = explode(', ', $timeString);
                $this->working_hours_parts[$day] = [
                    'is_closed' => false,
                    'slots' => [],
                ];

                foreach ($slots as $slot) {
                    if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i', $slot, $matches)) {
                        $this->working_hours_parts[$day]['slots'][] = [
                            'start_hour' => str_pad($matches[1], 2, '0', STR_PAD_LEFT),
                            'start_min' => $matches[2],
                            'start_period' => strtoupper($matches[3]),
                            'end_hour' => str_pad($matches[4], 2, '0', STR_PAD_LEFT),
                            'end_min' => $matches[5],
                            'end_period' => strtoupper($matches[6]),
                        ];
                    }
                }

                if (empty($this->working_hours_parts[$day]['slots'])) {
                    // Fallback
                    $this->working_hours_parts[$day]['slots'][] = [
                        'start_hour' => '09', 'start_min' => '00', 'start_period' => 'AM',
                        'end_hour' => '05', 'end_min' => '00', 'end_period' => 'PM',
                    ];
                }
            }
        }
    }

    public function addTimeRange($day)
    {
        $this->working_hours_parts[$day]['slots'][] = [
            'start_hour' => '09',
            'start_min' => '00',
            'start_period' => 'AM',
            'end_hour' => '05',
            'end_min' => '00',
            'end_period' => 'PM',
        ];
    }

    public function removeTimeRange($day, $index)
    {
        if (count($this->working_hours_parts[$day]['slots']) > 1) {
            unset($this->working_hours_parts[$day]['slots'][$index]);
            $this->working_hours_parts[$day]['slots'] = array_values($this->working_hours_parts[$day]['slots']);
        } else {
            $this->working_hours_parts[$day]['is_closed'] = true;
        }
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

        // Reconstruct working_hours from parts
        foreach ($this->working_hours_parts as $day => $data) {
            if ($data['is_closed'] || empty($data['slots'])) {
                $this->working_hours[$day] = 'Closed';
            } else {
                $slotStrings = [];
                foreach ($data['slots'] as $slot) {
                    $slotStrings[] = "{$slot['start_hour']}:{$slot['start_min']} {$slot['start_period']} - {$slot['end_hour']}:{$slot['end_min']} {$slot['end_period']}";
                }
                $this->working_hours[$day] = implode(', ', array_unique($slotStrings));
            }
        }

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
        $this->dispatch('scroll-to-top');
    }

    public function render()
    {
        return view('livewire.doctor.clinic-edit')
            ->layout('components.layouts.app', ['title' => 'Edit Clinic']);
    }
}
