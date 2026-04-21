<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CreateStaff extends Component
{
    public $name;
    public $email;
    public $phone;
    public $role_id;
    public $employee_id;
    public $department;
    public $joining_date;
    public $is_active = true;
    public $password;

    public function mount()
    {
        $this->employee_id = $this->generateEmployeeId();
        $this->joining_date = date('Y-m-d');
    }

    public function generateEmployeeId()
    {
        $lastUser = User::where('clinic_id', Auth::user()->clinic_id)
            ->whereNotNull('employee_id')
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastUser ? (int)substr($lastUser->employee_id, 4) + 1 : 1;
        return 'STF-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'required|string|unique:users,employee_id',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'clinic_id' => Auth::user()->clinic_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'employee_id' => $this->employee_id,
            'department' => $this->department,
            'joining_date' => $this->joining_date,
            'is_active' => $this->is_active,
            'password' => Hash::make($this->password),
        ]);

        $user->roles()->attach($this->role_id);

        session()->flash('success', 'Staff member invited successfully.');
        return $this->redirect(route('admin.staff.index'), navigate: true);
    }

    public function render()
    {
        $clinicId = Auth::user()->clinic_id;
        $roles = Role::where('name', '!=', 'patient')->get();

        $roleDistribution = [];
        foreach ($roles as $role) {
            $roleDistribution[] = [
                'name' => ucwords(str_replace('_', ' ', $role->name)),
                'count' => User::where('clinic_id', $clinicId)
                    ->whereHas('roles', fn($q) => $q->where('id', $role->id))
                    ->count()
            ];
        }

        $totalStaff = User::where('clinic_id', $clinicId)
            ->whereHas('roles', fn($q) => $q->where('name', '!=', 'patient'))
            ->count();

        return view('livewire.clinicAdmin.staff.createStaff', [
            'roles' => $roles,
            'roleDistribution' => $roleDistribution,
            'totalStaff' => $totalStaff
        ])->layout('components.layouts.app');
    }
}