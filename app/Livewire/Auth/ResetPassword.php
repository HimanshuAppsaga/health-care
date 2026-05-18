<?php

namespace App\Livewire\Auth;

use App\Services\AuthenticationService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Reset Password | Indigo Clinical')]
class ResetPassword extends Component
{
    public string $token;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:8|same:password_confirmation')]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(AuthenticationService $authService)
    {
        $this->validate();

        try {
            $statusMessage = $authService->resetPassword([
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);

            session()->flash('status', $statusMessage);

            return redirect()->route('login');
        } catch (ValidationException $e) {
            $this->addError('email', $e->errors()['email'][0] ?? trans('passwords.token'));
        }
    }

    public function render()
    {
        return view('livewire.auth.resetPassword');
    }
}
