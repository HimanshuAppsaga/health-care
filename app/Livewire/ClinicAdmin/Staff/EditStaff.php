<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class EditStaff extends Component
{
    public $userId;
    public $name;
    public $email;
    public $phone;
    public $role_id;
    public $employee_id;
    public $department;
    public $joining_date;
    public $is_active;
    public $password;

    public function mount($id)
    {
        $user = User::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role_id = $user->roles->first()?->id;
        $this->employee_id = $user->employee_id;
        $this->department = $user->department;
        $this->joining_date = $user->joining_date;
        $this->is_active = $user->is_active;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'password' => 'nullable|string|min:8',
        ]);

        $user = User::where('clinic_id', Auth::user()->clinic_id)->findOrFail($this->userId);
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department' => $this->department,
            'joining_date' => $this->joining_date,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        // Update Role
        $user->roles()->sync([$this->role_id]);

        session()->flash('success', 'Staff member updated successfully.');
        return $this->redirect(route('admin.staff.index'), navigate: true);
    }

    public function render()
    {
        $roles = Role::where('name', '!=', 'patient')->get();
        return view('livewire.clinicAdmin.staff.editStaff', [
            'roles' => $roles
        ])->layout('components.layouts.app');
    }
}
