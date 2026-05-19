<?php

namespace App\Livewire\Common;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Header extends Component
{
    public ?string $title = null;

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.common.header');
    }
}
