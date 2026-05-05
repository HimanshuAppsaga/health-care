<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use App\Models\Clinic;

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
            ->layout('layouts.app');
    }
}