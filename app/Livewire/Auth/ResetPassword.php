<?php

namespace App\Livewire\Auth;

use App\Services\AuthenticationService;
use Illuminate\Support\Facades\Cache;
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

    #[Validate('required|numeric|digits:4')]
    public string $otp = '';

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
                'otp' => $this->otp,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);

            session()->flash('status', $statusMessage);

            return $this->redirect(route('login'), navigate: true);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['otp'])) {
                $this->addError('otp', $errors['otp'][0]);
            }
            if (isset($errors['email'])) {
                $this->addError('email', $errors['email'][0]);
            }
        }
    }

    public function render()
    {
        return view('livewire.auth.resetPassword');
    }
}
