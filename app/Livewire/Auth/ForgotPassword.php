<?php

namespace App\Livewire\Auth;

use App\Services\AuthenticationService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Forgot Password | Indigo Clinical')]
class ForgotPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public string $status = '';

    public function sendResetLink(AuthenticationService $authService)
    {
        $this->validate();

        try {
            $message = $authService->forgotPassword(['email' => $this->email]);
            $this->status = $message;
            $this->email = '';
        } catch (ValidationException $e) {
            $this->addError('email', $e->errors()['email'][0] ?? trans('passwords.user'));
        }
    }

    public function render()
    {
        return view('livewire.auth.forgetPassword');
    }
}
