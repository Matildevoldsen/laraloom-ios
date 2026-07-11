<?php

namespace App\NativeComponents\Laraloom;

use App\Icons\Android;
use App\Icons\Ios;
use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Throwable;

class Profile extends NativeComponent
{
    /** @var array<string, mixed> */
    public array $user = [];

    /** @var array<int, array<string, mixed>> */
    public array $posts = [];

    public string $error = '';

    public function mount(): void
    {
        $this->refresh();
    }

    public function navTitle(): string
    {
        return 'You';
    }

    public function showsNavBack(): bool
    {
        return false;
    }

    public function refresh(): void
    {
        if (! $this->api()->hasToken()) {
            $this->user = [];
            $this->posts = [];

            return;
        }

        try {
            $this->user = $this->api()->me();
            $this->posts = $this->api()->myPosts()['data'];
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'Your profile could not be refreshed.';
        }
    }

    public function signIn(): void
    {
        $this->navigate('/login')->transition(Transition::SlideFromBottom);
    }

    public function createAccount(): void
    {
        $this->navigate('/register')->transition(Transition::SlideFromBottom);
    }

    public function openAdmin(): void
    {
        if ($this->user['is_admin'] ?? false) {
            $this->navigate('/admin')->transition(Transition::SlideFromRight);
        }
    }

    public function openDeleteAccount(): void
    {
        $this->navigate('/account/delete')->transition(Transition::SlideFromRight);
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, NavAction>
     */
    public function postMenu(array $post): array
    {
        $postId = (int) $post['id'];

        return [
            NavAction::make("edit-{$postId}")
                ->label('Edit post')
                ->icon(ios: Ios::Pencil, android: Android::Edit)
                ->press("editPost({$postId})"),
            NavAction::divider(),
            NavAction::make("delete-{$postId}")
                ->label('Delete post')
                ->icon(ios: Ios::Trash, android: Android::Delete)
                ->destructive()
                ->press("confirmDelete({$postId})"),
        ];
    }

    public function editPost(int $postId): void
    {
        $this->navigate("/posts/{$postId}/edit")->transition(Transition::SlideFromRight);
    }

    public function confirmDelete(int $postId): void
    {
        $alert = Dialog::alert(
            'Delete this post?',
            'This permanently removes it from Laraloom.',
            ['Cancel', 'Delete'],
        );
        $alert->buttonPressed(function (ButtonPressed $event) use ($postId): void {
            if ($event->label === 'Delete') {
                $this->deletePost($postId);
            }
        });
        $alert->show();
    }

    public function deletePost(int $postId): void
    {
        try {
            $this->api()->deletePost($postId);
            $this->refresh();
        } catch (Throwable) {
            $this->error = 'That post could not be deleted.';
        }
    }

    /** @return array<int, array{title: string, subtitle: string, leading: string, url: string}> */
    public function onSearchQuery(string $query): array
    {
        if (trim($query) === '') {
            return [];
        }

        try {
            return array_map(
                fn (array $post): array => [
                    'title' => (string) ($post['title'] ?? 'Community post'),
                    'subtitle' => (string) ($post['summary'] ?? $post['source']['name'] ?? ''),
                    'leading' => 'newspaper',
                    'url' => '/posts/'.$post['id'],
                ],
                $this->api()->searchFeed($query)['data'],
            );
        } catch (Throwable) {
            return [];
        }
    }

    public function signOut(): void
    {
        try {
            $this->api()->logout();
            $this->user = [];
            $this->posts = [];
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'Sign out did not complete. Please try again.';
        }
    }

    public function render(): View
    {
        return view('native.laraloom.profile');
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
