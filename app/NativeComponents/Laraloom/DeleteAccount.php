<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Throwable;

class DeleteAccount extends NativeComponent
{
    public string $password = '';

    public string $error = '';

    public bool $isSubmitting = false;

    public function mount(): void
    {
        if (! $this->api()->hasToken()) {
            $this->replace('/login')->transition(Transition::Fade);
        }
    }

    public function navTitle(): string
    {
        return 'Delete account';
    }

    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    public function updatePassword(string $value): void
    {
        $this->password = $value;
    }

    public function confirmDeletion(): void
    {
        if ($this->password === '') {
            $this->error = 'Enter your password to continue.';

            return;
        }

        $alert = Dialog::alert(
            'Permanently delete your account?',
            'Your profile, posts, replies and saved activity will be removed. This cannot be undone.',
            ['Cancel', 'Delete my account'],
        );
        $alert->buttonPressed(function (ButtonPressed $event): void {
            if ($event->label === 'Delete my account') {
                $this->deleteAccount();
            }
        });
        $alert->show();
    }

    public function deleteAccount(): void
    {
        $this->isSubmitting = true;
        $this->error = '';

        try {
            $this->api()->deleteAccount($this->password);
            $this->replace('/profile')->transition(Transition::Fade);
        } catch (RequestException $exception) {
            $this->error = $exception->response->status() === 422
                ? 'That password is not correct.'
                : 'Your account could not be deleted.';
        } catch (Throwable) {
            $this->error = 'Your account could not be deleted.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render(): View
    {
        return view('native.laraloom.delete-account');
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
