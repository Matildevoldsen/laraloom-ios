<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Throwable;

class Compose extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    public int $kindIndex = 0;

    public string $title = '';

    public string $body = '';

    public string $url = '';

    public string $tags = '';

    public bool $isSubmitting = false;

    public string $error = '';

    public function mount(): void
    {
        if (! app(LaraloomApiClient::class)->hasToken()) {
            $this->replace('/login')->transition(Transition::Fade);
        }
    }

    public function navTitle(): string
    {
        return 'Share with Laraloom';
    }

    public function updateKind(int $index): void
    {
        $this->kindIndex = max(0, min(3, $index));
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
        $kind = ['note', 'article', 'package', 'project'][$this->kindIndex];

        if ($this->body === '' && $this->url === '') {
            $this->error = 'Write something or include a link.';

            return;
        }

        if ($kind !== 'note' && trim($this->title) === '') {
            $this->error = 'Add a title for this kind of post.';

            return;
        }

        $this->isSubmitting = true;
        $this->error = '';

        try {
            $post = app(LaraloomApiClient::class)->createPost(array_filter([
                'kind' => $kind,
                'title' => trim($this->title),
                'body' => trim($this->body),
                'url' => $this->url,
                'tags' => $this->tags,
            ], fn (string $value): bool => $value !== ''));
            $this->replace('/posts/'.$post['id'])->transition(Transition::SlideFromRight);
        } catch (Throwable) {
            $this->error = 'Your post could not be published. Check the fields and try again.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render(): View
    {
        return view('native.laraloom.compose');
    }
}
