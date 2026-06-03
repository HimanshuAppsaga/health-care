<?php

namespace App\Livewire\Doctor;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Assign Role | Clinic Saga')]
class AssignRole extends Component
{
    #[Validate('required|email|exists:users,email', message: [
        'exists' => 'The entered email is not registered in our database.',
    ])]
    public string $email = '';

    #[Validate('required|in:1,2', message: [
        'in' => 'Please select a valid role.',
    ])]
    public string $role_id = '';

    /**
     * Update the user's role in the database.
     */
    public function assign(): void
    {
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            session()->flash('error', 'User not found.');

            return;
        }

        $oldRole = $user->role?->name;
        $newRole = Role::find($this->role_id)?->name;

        if ($oldRole === $newRole) {
            session()->flash('error', "{$user->name} already has the role of {$newRole}.");

            return;
        }

        $user->role_id = (int) $this->role_id;
        $user->save();

        $user->refresh();

        if ($user->hasRole('doctor')) {
            $user->ensureDoctorProfileExists();
        } else {
            $user->doctor()->delete();
        }

        session()->flash('message', "Role updated successfully! {$user->name} is now a ".ucfirst($newRole).'.');
        $this->reset(['email', 'role_id']);
    }

    public function render()
    {
        return view('livewire.doctor.assign-role');
    }
}
