<?php

namespace App\Livewire\Auth;

use App\Services\AuthenticationService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login | Indigo Clinical')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function authenticate(AuthenticationService $authService)
    {
        $this->validate();

        try {
            $user = $authService->login([
                'email' => $this->email,
                'password' => $this->password,
            ], $this->remember);

            session()->regenerate();

            return $this->redirect(route($user->getDashboardRouteName()), navigate: true);
        } catch (ValidationException $e) {
            $this->addError('email', $e->errors()['email'][0] ?? trans('auth.failed'));
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
