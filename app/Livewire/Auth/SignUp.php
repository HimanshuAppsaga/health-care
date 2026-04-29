<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Sign Up | Indigo Clinical')]
class SignUp extends Component
{
    #[Validate('required|string|max:255')]
    public string $full_name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|same:password_confirmation')]
    public string $password = '';

    public string $password_confirmation = '';

    #[Validate('accepted')]
    public bool $terms = false;

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->full_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'phone' => '', // Temporary empty phone
            'is_active' => true,
        ]);

        Auth::login($user);

        return redirect()->intended(route($user->getDashboardRouteName()));
    }

    public function render()
    {
        return view('livewire.auth.signup');
    }
}
