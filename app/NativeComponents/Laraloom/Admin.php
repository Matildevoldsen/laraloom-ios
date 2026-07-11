<?php

namespace App\NativeComponents\Laraloom;

use App\Icons\Android;
use App\Icons\Ios;
use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Throwable;

class Admin extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    /** @var array<string, int> */
    public array $counts = [];

    /** @var array<int, array<string, mixed>> */
    public array $posts = [];

    public string $error = '';

    public function mount(): void
    {
        if (! $this->api()->hasToken()) {
            $this->replace('/login')->transition(Transition::Fade);

            return;
        }

        try {
            $user = $this->api()->me();

            if (! ($user['is_admin'] ?? false)) {
                $this->replace('/profile')->transition(Transition::Fade);

                return;
            }

            $this->refresh();
        } catch (Throwable) {
            $this->error = 'Admin tools could not be opened.';
        }
    }

    public function navTitle(): string
    {
        return 'Laraloom admin';
    }

    public function refresh(): void
    {
        try {
            $dashboard = $this->api()->adminDashboard();
            $this->counts = is_array($dashboard['counts'] ?? null) ? $dashboard['counts'] : [];
            $this->posts = is_array($dashboard['posts'] ?? null) ? $dashboard['posts'] : [];
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'Moderation data could not be refreshed.';
        }
    }

    public function publish(int $postId): void
    {
        $this->moderate($postId, 'published');
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, NavAction>
     */
    public function postMenu(array $post): array
    {
        $postId = (int) $post['id'];
        $actions = [];

        if (($post['status'] ?? '') !== 'published') {
            $actions[] = NavAction::make("publish-{$postId}")
                ->label('Publish')
                ->icon(ios: Ios::CheckmarkCircle, android: Android::CheckCircle)
                ->press("publish({$postId})");
        }

        $actions[] = NavAction::make("edit-{$postId}")
            ->label('Edit')
            ->icon(ios: Ios::Pencil, android: Android::Edit)
            ->press("edit({$postId})");

        if (($post['status'] ?? '') !== 'rejected') {
            $actions[] = NavAction::make("reject-{$postId}")
                ->label('Reject')
                ->icon(ios: Ios::XmarkCircle, android: Android::Cancel)
                ->press("reject({$postId})");
        }

        $actions[] = NavAction::divider();
        $actions[] = NavAction::make("delete-{$postId}")
            ->label('Delete permanently')
            ->icon(ios: Ios::Trash, android: Android::Delete)
            ->destructive()
            ->press("confirmDelete({$postId})");

        return $actions;
    }

    public function reject(int $postId): void
    {
        $this->moderate($postId, 'rejected');
    }

    public function edit(int $postId): void
    {
        $this->navigate("/posts/{$postId}/edit")->transition(Transition::SlideFromRight);
    }

    public function confirmDelete(int $postId): void
    {
        $alert = Dialog::alert(
            'Delete this post?',
            'This moderation action cannot be undone.',
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

    public function render(): View
    {
        return view('native.laraloom.admin');
    }

    private function moderate(int $postId, string $status): void
    {
        try {
            $this->api()->moderatePost($postId, $status);
            $this->refresh();
        } catch (Throwable) {
            $this->error = 'That moderation action did not complete.';
        }
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
