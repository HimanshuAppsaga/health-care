<?php

namespace App\Livewire\Doctor;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DoctorEdit extends Component
{
    use WithFileUploads;

    public $doctor;

    public $user;

    // User fields
    public $name;

    public $phone;

    public $bio;

    // Doctor fields
    public $specialization;

    public $qualification;

    public $experience_years;

    public $consultation_fee;

    public $working_hours = [];

    public $working_hours_parts = [];

    public $photo; // For new upload

    public $removePhoto = false;

    public function mount($id)
    {
        $this->doctor = Doctor::with('user')->findOrFail($id);
        $this->user = $this->doctor->user;

        // Populate fields
        $this->name = $this->user->name;
        $this->phone = $this->user->phone;
        $this->bio = $this->user->bio;

        $this->specialization = $this->doctor->specialization;
        $this->qualification = $this->doctor->qualification;
        $this->experience_years = $this->doctor->experience_years;
        $this->consultation_fee = $this->doctor->consultation_fee;

        $this->working_hours = $this->doctor->working_hours ?? [];
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
                $slots = is_array($timeString) ? $timeString : explode(', ', $timeString);
                $this->working_hours_parts[$day] = [
                    'is_closed' => false,
                    'slots' => [],
                ];

                foreach ($slots as $slot) {
                    if (is_string($slot) && preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)\s*-\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i', $slot, $matches)) {
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

    public function removeExistingPhoto()
    {
        $this->removePhoto = true;
    }

    public function cancelNewPhoto()
    {
        $this->photo = null;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'specialization' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);

        // Reconstruct working_hours
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

        // Handle photo
        if ($this->removePhoto && $this->user->profile_photo_path) {
            if (Storage::disk('public')->exists($this->user->profile_photo_path)) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $this->user->profile_photo_path = null;
        }

        if ($this->photo) {
            if ($this->user->profile_photo_path && Storage::disk('public')->exists($this->user->profile_photo_path)) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $this->user->profile_photo_path = $this->photo->store('profile-photos', 'public');
        }

        // Update User
        $this->user->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'profile_photo_path' => $this->user->profile_photo_path,
        ]);

        // Update Doctor
        $this->doctor->update([
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'consultation_fee' => $this->consultation_fee,
            'working_hours' => $this->working_hours,
        ]);

        $this->photo = null;
        $this->removePhoto = false;

        session()->flash('success', 'Doctor profile updated successfully!');
        $this->dispatch('scroll-to-top');
    }

    public function render()
    {
        return view('livewire.doctor.doctorEdit')
            ->layout('components.layouts.app', ['title' => 'Edit Doctor Profile']);
    }
}
