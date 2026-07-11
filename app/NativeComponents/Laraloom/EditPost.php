<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Throwable;

class EditPost extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    public int $postId = 0;

    public int $kindIndex = 0;

    public string $title = '';

    public string $body = '';

    public string $url = '';

    public string $tags = '';

    public bool $isSubmitting = false;

    public string $error = '';

    /** @var array<int, string> */
    private array $kinds = ['note', 'article', 'package', 'project'];

    public function mount(): void
    {
        if (! $this->api()->hasToken()) {
            $this->replace('/login')->transition(Transition::Fade);

            return;
        }

        try {
            $post = $this->api()->post((int) $this->param('id'));

            if (! ($post['permissions']['update'] ?? false)) {
                $this->error = 'You do not have permission to edit this post.';

                return;
            }

            $this->postId = (int) $post['id'];
            $this->kindIndex = array_search($post['kind'], $this->kinds, true) ?: 0;
            $this->title = (string) ($post['title'] ?? '');
            $this->body = (string) ($post['body'] ?? '');
            $this->url = (string) ($post['url'] ?? '');
            $this->tags = implode(', ', $post['tags'] ?? []);
        } catch (Throwable) {
            $this->error = 'This post could not be loaded for editing.';
        }
    }

    public function navTitle(): string
    {
        return 'Edit post';
    }

    public function updateKind(int $index): void
    {
        $this->kindIndex = max(0, min(count($this->kinds) - 1, $index));
    }

    public function updateTitle(string $value): void
    {
        $this->title = $value;
    }

    public function updateBody(string $value): void
    {
        $this->body = $value;
    }

    public function updateUrl(string $value): void
    {
        $this->url = trim($value);
    }

    public function updateTags(string $value): void
    {
        $this->tags = $value;
    }

    public function submit(): void
    {
        if ($this->postId === 0 || ($this->body === '' && $this->url === '')) {
            $this->error = 'Write something or include a link.';

            return;
        }

        $this->isSubmitting = true;
        $this->error = '';

        try {
            $this->api()->updatePost($this->postId, array_filter([
                'kind' => $this->kinds[$this->kindIndex],
                'title' => trim($this->title),
                'body' => trim($this->body),
                'url' => $this->url,
                'tags' => $this->tags,
            ], fn (string $value): bool => $value !== ''));
            $this->replace('/posts/'.$this->postId)->transition(Transition::Fade);
        } catch (Throwable) {
            $this->error = 'Your changes could not be saved. Check the fields and try again.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function confirmDelete(): void
    {
        $alert = Dialog::alert(
            'Delete this post?',
            'This permanently removes it from Laraloom.',
            ['Cancel', 'Delete'],
        );
        $alert->buttonPressed(function (ButtonPressed $event): void {
            if ($event->label === 'Delete') {
                $this->deletePost();
            }
        });
        $alert->show();
    }

    public function deletePost(): void
    {
        try {
            $this->api()->deletePost($this->postId);
            $this->replace('/profile')->transition(Transition::Fade);
        } catch (Throwable) {
            $this->error = 'This post could not be deleted.';
        }
    }

    public function render(): View
    {
        return view('native.laraloom.edit-post');
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
