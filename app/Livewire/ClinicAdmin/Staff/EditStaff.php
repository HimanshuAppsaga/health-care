<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditStaff extends Component
{
    use WithFileUploads;

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

    public $emergency_contact_name;

    public $emergency_contact_phone;

    public $address;

    public $unit;

    public $supervisor_name;

    public $bio;

    public $photo;

    public $currentPhoto;

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
        $this->emergency_contact_name = $user->emergency_contact_name;
        $this->emergency_contact_phone = $user->emergency_contact_phone;
        $this->address = $user->address;
        $this->unit = $user->unit;
        $this->supervisor_name = $user->supervisor_name;
        $this->bio = $user->bio;
        $this->currentPhoto = $user->profile_photo_path;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->userId,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'password' => 'nullable|string|min:8',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'unit' => 'nullable|string|max:255',
            'supervisor_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
        ]);

        try {
            $user = User::where('clinic_id', Auth::user()->clinic_id)->findOrFail($this->userId);

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'department' => $this->department,
                'joining_date' => $this->joining_date,
                'bio' => $this->bio,
                'is_active' => $this->is_active,
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
                'address' => $this->address,
                'unit' => $this->unit,
                'supervisor_name' => $this->supervisor_name,
                'rating' => $this->rating,
            ];

            if ($this->photo) {
                $path = $this->photo->store('profile-photos', 'public');
                $data['profile_photo_path'] = $path;
            }

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            // Sync Role
            $user->syncRoles([Role::find($this->role_id)->name]);

            session()->flash('success', 'Staff profile updated successfully.');

            return $this->redirect(route('admin.staff.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating staff: '.$e->getMessage());
        }
    }

    public function render()
    {
        $roles = Role::where('name', '!=', 'patient')->get();

        return view('livewire.clinicAdmin.staff.editStaff', [
            'roles' => $roles,
        ])->layout('components.layouts.app');
    }
}
