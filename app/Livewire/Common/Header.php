<?php

namespace App\Livewire\Common;

use Livewire\Component;

class Header extends Component
{
    public $title;

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.common.header');
    }
}
