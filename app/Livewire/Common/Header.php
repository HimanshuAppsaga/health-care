<?php

namespace App\Livewire\Common;

use App\Services\LogoutService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class Header extends Component
{
    public ?string $title = null;

    public function logout(LogoutService $logoutService)
    {
        $logoutService->logoutUser();

        return Redirect::route('login');
    }

    public function render()
    {
        return View::make('livewire.common.header');
    }
}
