<?php

namespace App\Livewire\Common;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class Header extends Component
{
    public ?string $title = null;

    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        return Redirect::route('login');
    }

    public function render()
    {
        return View::make('livewire.common.header');
    }
}
