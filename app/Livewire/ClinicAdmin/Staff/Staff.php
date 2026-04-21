<?php

namespace App\Livewire\ClinicAdmin\Staff;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Staff extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus($userId)
    {
        $user = User::where('clinic_id', Auth::user()->clinic_id)->findOrFail($userId);
        $user->is_active = !$user->is_active;
        $user->save();

        session()->flash('success', 'Staff status updated successfully.');
    }

    public function deleteStaff($userId)
    {
        $user = User::where('clinic_id', Auth::user()->clinic_id)->findOrFail($userId);
        
        // Prevent deleting self
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        $user->delete();
        session()->flash('success', 'Staff member removed successfully.');
    }

    public function resetFilters()
    {
        $this->reset(['search', 'roleFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $clinicId = Auth::user()->clinic_id;

        $query = User::where('clinic_id', $clinicId)
            ->whereHas('roles', function($q) {
                $q->where('name', '!=', 'patient');
            })
            ->with('roles');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        $staffMembers = $query->latest()->paginate(10);
        
        $roles = Role::where('name', '!=', 'patient')->get();
        $totalActiveStaff = User::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', '!=', 'patient'))
            ->count();

        return view('livewire.clinicAdmin.staff.staff', [
            'staffMembers' => $staffMembers,
            'roles' => $roles,
            'totalActiveStaff' => $totalActiveStaff
        ])->layout('components.layouts.app');
    }
}
