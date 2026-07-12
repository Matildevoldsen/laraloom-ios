<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Gallery\MediaSelected;
use Native\Mobile\Facades\Camera;
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

    /** @var array<int, string> */
    public array $mediaPaths = [];

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

    public function chooseMedia(): void
    {
        Camera::pickImages('all', true, 4)
            ->mediaSelected(function (MediaSelected $event): void {
                if (! $event->success || $event->cancelled) {
                    return;
                }

                $this->mediaPaths = array_values(array_filter(array_map(
                    fn (mixed $file): ?string => $this->mediaPath($file),
                    $event->files,
                )));
                $this->error = '';
            });
    }

    public function clearMedia(): void
    {
        $this->mediaPaths = [];
    }

    public function submit(): void
    {
        $kind = ['note', 'article', 'package', 'project'][$this->kindIndex];
        $title = trim($this->title);
        $body = trim($this->body);
        $url = trim($this->url);

        if ($body === '' && $url === '' && $this->mediaPaths === []) {
            $this->error = 'Write something, include a link or add media.';

            return;
        }

        if ($kind !== 'note' && $title === '') {
            $this->error = 'Add a title for this kind of post.';

            return;
        }

        $this->isSubmitting = true;
        $this->error = '';

        try {
            $post = app(LaraloomApiClient::class)->createPost(array_filter([
                'kind' => $kind,
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'tags' => $this->tags,
            ], fn (string $value): bool => $value !== ''), $this->mediaPaths);
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

    private function mediaPath(mixed $file): ?string
    {
        if (is_string($file)) {
            return $file;
        }

        if (! is_array($file)) {
            return null;
        }

        $path = $file['path'] ?? $file['url'] ?? null;

        return is_string($path) ? $path : null;
    }
}
