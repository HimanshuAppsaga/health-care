<?php

namespace App\Livewire\Common;

use App\Services\SidebarConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public array $menuItems = [];

    public bool $isCollapsed = false;

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $role = $user->roles()->first()?->name;
            if ($role) {
                $this->menuItems = SidebarConfig::getMenuForRole($role);
            }
        }
    }

    public function toggleSidebar()
    {
        $this->isCollapsed = ! $this->isCollapsed;
    }

    public function render()
    {
        return view('livewire.common.sidebar');
    }
}
