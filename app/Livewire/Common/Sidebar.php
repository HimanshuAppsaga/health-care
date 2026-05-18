<?php

namespace App\Livewire\Common;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Services\SidebarConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public array $menuItems = [];

    public bool $isCollapsed = false;

    public string $title = 'ClinicOS';

    public string $subtitle = 'Admin Console';

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $role = $user->role?->name;
            if ($role) {
                $this->menuItems = SidebarConfig::getMenuForRole($role);

                // Make title and subtitle dynamic
                if ($user->hasRole('doctor') && $user->doctor?->clinic) {
                    $this->title = $user->doctor->clinic->name;
                } elseif ($user->hasRole('patient') && $user->patient?->clinic) {
                    $this->title = $user->patient->clinic->name;
                } elseif ($user->hasRole('receptionist')) {
                    $selectedDoctorId = session('receptionist_selected_doctor_id');
                    $clinic = null;

                    if ($selectedDoctorId) {
                        $doctor = Doctor::find($selectedDoctorId);
                        if ($doctor && $doctor->clinic) {
                            $clinic = $doctor->clinic;
                        }
                    }

                    if (! $clinic) {
                        $clinic = Clinic::first();
                    }

                    if ($clinic) {
                        $this->title = $clinic->name;
                    } else {
                        $this->title = config('app.name', 'ClinicOS');
                    }
                } else {
                    $this->title = config('app.name', 'ClinicOS');
                }

                $this->subtitle = match ($role) {
                    'doctor' => 'Doctor Console',
                    'receptionist' => 'Receptionist Console',
                    'patient' => 'Patient Portal',
                    default => 'Admin Console',
                };
            }
        }
    }

    public function toggleSidebar()
    {
        $this->isCollapsed = ! $this->isCollapsed;
    }

    public function render()
    {
        return view('livewire.common.sidebar');
    }
}
