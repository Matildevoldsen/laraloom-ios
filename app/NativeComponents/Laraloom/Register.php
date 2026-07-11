<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Throwable;

class Register extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public bool $isSubmitting = false;

    public string $error = '';

    public function navTitle(): string
    {
        return 'Create account';
    }

    public function submit(): void
    {
        $name = trim($this->name);
        $username = mb_strtolower(trim($this->username));
        $email = mb_strtolower(trim($this->email));

        if ($name === '' || $username === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'Add your name, username and a valid email.';

            return;
        }

        if ($this->password === '' || $this->password !== $this->passwordConfirmation) {
            $this->error = 'Use matching passwords.';

            return;
        }

        $this->isSubmitting = true;
        $this->error = '';

        try {
            app(LaraloomApiClient::class)->register(
                $name,
                $username,
                $email,
                $this->password,
                $this->passwordConfirmation,
                'Laraloom mobile',
            );
            $this->password = '';
            $this->passwordConfirmation = '';
            $this->replace('/profile')->transition(Transition::Fade);
        } catch (RequestException $exception) {
            $this->error = $this->validationMessage($exception);
        } catch (Throwable) {
            $this->error = 'Your account could not be created. Check your connection and try again.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render(): View
    {
        return view('native.laraloom.register');
    }

    private function validationMessage(RequestException $exception): string
    {
        $errors = $exception->response->json('errors');

        if (! is_array($errors)) {
            return 'Those details were not accepted.';
        }

        foreach ($errors as $messages) {
            if (is_array($messages) && is_string($messages[0] ?? null)) {
                return $messages[0];
            }
        }

        return 'Those details were not accepted.';
    }
}
