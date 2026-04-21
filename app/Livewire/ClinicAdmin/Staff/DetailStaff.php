<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DetailStaff extends Component
{
    public $staff;
    public $activities = [];

    public $permissions = [];
    public $appointmentCount = 0;

    public function mount($id)
    {
        $this->staff = User::where('clinic_id', Auth::user()->clinic_id)
            ->with(['roles.permissions'])
            ->findOrFail($id);

        // Get all permissions for this user
        $this->permissions = $this->staff->roles->flatMap->permissions->pluck('name', 'slug')->unique();

        // If doctor, get appointment count
        if ($this->staff->hasRole('doctor')) {
            $doctor = \App\Models\Doctor::where('user_id', $this->staff->id)->first();
            if ($doctor) {
                $this->appointmentCount = \App\Models\Appointment::where('doctor_id', $doctor->id)->count();
            }
        }

        // Mock activities for now (still no activity_logs table)
        $this->activities = [
            [
                'type' => 'login',
                'title' => 'System Login',
                'description' => 'Workstation ' . request()->ip(),
                'time' => 'Recently',
                'status' => 'Success',
                'icon' => 'log-in'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.clinicAdmin.staff.detailStaff')
            ->layout('components.layouts.app');
    }
}
