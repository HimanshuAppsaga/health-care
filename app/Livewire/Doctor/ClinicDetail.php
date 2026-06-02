<?php

namespace App\Livewire\Doctor;

use App\Models\Clinic;
use Livewire\Component;

class ClinicDetail extends Component
{
    public $clinic;

    public function mount($id)
    {
        $this->clinic = Clinic::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.doctor.clinicDetail')
            ->layout('components.layouts.app', ['title' => 'Clinic Details']);
    }
}
