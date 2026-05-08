<?php

namespace App\Livewire\Doctor;

use App\Models\Doctor;
use Livewire\Component;

class DoctorDetail extends Component
{
    public $doctor;

    public function mount($id)
    {
        $this->doctor = Doctor::with('user')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.doctor.doctorDetail')
            ->layout('components.layouts.app', ['title' => 'Doctor Profile']);
    }
}
