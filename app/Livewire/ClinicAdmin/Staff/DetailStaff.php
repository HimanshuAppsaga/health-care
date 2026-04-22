<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DetailStaff extends Component
{
    public $staff;

    public $activities = [];

    public $permissions = [];

    public $appointmentCount = 0;

    public $weeklyActivity = [];

    public function mount($id)
    {
        $this->staff = User::where('clinic_id', Auth::user()->clinic_id)
            ->with(['roles.permissions'])
            ->findOrFail($id);

        // Get all permissions for this user
        $this->permissions = $this->staff->roles->flatMap->permissions->pluck('name', 'slug')->unique();

        // If doctor, get appointment count and weekly stats
        if ($this->staff->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $this->staff->id)->first();
            if ($doctor) {
                $this->appointmentCount = Appointment::where('doctor_id', $doctor->id)->count();

                // Fetch weekly activity (last 7 days)
                $this->weeklyActivity = collect(range(0, 6))->map(function ($days) use ($doctor) {
                    return Appointment::where('doctor_id', $doctor->id)
                        ->whereDate('appointment_date', now()->subDays($days))
                        ->count();
                })->reverse()->values()->all();
            }
        } else {
            // Default sparkline for non-doctors
            $this->weeklyActivity = [30, 45, 35, 50, 40, 60, 70];
        }

        // More dynamic activities
        $this->activities = [
            [
                'type' => 'update',
                'title' => 'Profile Updated',
                'description' => 'Last profile change recorded.',
                'time' => $this->staff->updated_at->diffForHumans(),
                'status' => 'Completed',
                'icon' => 'pencil',
            ],
            [
                'type' => 'login',
                'title' => 'System Login',
                'description' => 'Workstation '.request()->ip(),
                'time' => $this->staff->last_login_at ? $this->staff->last_login_at->diffForHumans() : 'Recently',
                'status' => 'Success',
                'icon' => 'log-in',
            ],
            [
                'type' => 'join',
                'title' => 'Joined Clinic',
                'description' => 'Official onboarding completed.',
                'time' => $this->staff->joining_date ? Carbon::parse($this->staff->joining_date)->diffForHumans() : $this->staff->created_at->diffForHumans(),
                'status' => 'Official',
                'icon' => 'user-plus',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.clinicAdmin.staff.detailStaff')
            ->layout('components.layouts.app');
    }
}
