<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Throwable;

class Login extends NativeComponent
{
    public string $email = '';

    public string $password = '';

    public bool $isSubmitting = false;

    public string $error = '';

    public function navTitle(): string
    {
        return 'Sign in';
    }

    public function submit(): void
    {
        $email = mb_strtolower(trim($this->email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $this->password === '') {
            $this->error = 'Enter your email and password.';

            return;
        }

        $this->isSubmitting = true;
        $this->error = '';

        try {
            app(LaraloomApiClient::class)->login($email, $this->password, 'Laraloom for iPhone');
            $this->password = '';
            $this->replace('/profile')->transition(Transition::Fade);
        } catch (Throwable) {
            $this->error = 'Those details were not accepted. Check them and try again.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function createAccount(): void
    {
        $this->navigate('/register')->transition(Transition::SlideFromRight);
    }

    public function render(): View
    {
        return view('native.laraloom.login');
    }
}
