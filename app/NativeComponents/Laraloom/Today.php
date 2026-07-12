<?php

namespace App\NativeComponents\Laraloom;

use App\Icons\Android;
use App\Icons\Ios;
use App\Services\LaraloomApiClient;
use App\Services\LaraloomRealtime;
use Illuminate\View\View;
use Matildevoldsen\NativeWebSockets\Events\MessageReceived;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Throwable;

class Today extends NativeComponent
{
    /** @var array<int, array<string, mixed>> */
    public array $posts = [];

    public int $selectedFeed = 0;

    public ?string $nextCursor = null;

    public bool $isLoadingMore = false;

    public string $error = '';

    public function mount(): void
    {
        $this->refresh();
        app(LaraloomRealtime::class)->subscribeToFeed();
    }

    #[On(MessageReceived::class)]
    public function handleRealtimeActivity(string $event): void
    {
        if ($event === LaraloomRealtime::ActivityEvent) {
            $this->refresh();
        }
    }

    public function navTitle(): string
    {
        return 'Laraloom';
    }

    public function showsNavBack(): bool
    {
        return false;
    }

    public function refresh(): void
    {
        try {
            $result = $this->selectedFeed === 1
                ? $this->api()->followingFeed()
                : $this->api()->feed();
            $this->posts = $result['data'];
            $this->nextCursor = is_string($result['meta']['next_cursor'] ?? null)
                ? $result['meta']['next_cursor']
                : null;
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'Laraloom could not refresh. Check your connection and try again.';
        }
    }

    public function loadMore(): void
    {
        if ($this->nextCursor === null || $this->isLoadingMore) {
            return;
        }

        $this->isLoadingMore = true;

        try {
            $result = $this->selectedFeed === 1
                ? $this->api()->followingFeed($this->nextCursor)
                : $this->api()->feed($this->nextCursor);
            $this->posts = collect([...$this->posts, ...$result['data']])
                ->unique('id')
                ->values()
                ->all();
            $this->nextCursor = is_string($result['meta']['next_cursor'] ?? null)
                ? $result['meta']['next_cursor']
                : null;
        } catch (Throwable) {
            $this->error = 'More posts could not be loaded.';
        } finally {
            $this->isLoadingMore = false;
        }
    }

    public function selectFeed(int $index): void
    {
        if ($index === 1 && ! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        $this->selectedFeed = $index;
        $this->refresh();
    }

    public function openPost(int $id): void
    {
        $this->navigate("/posts/{$id}")->transition(Transition::SlideFromRight);
    }

    public function compose(): void
    {
        $destination = $this->api()->hasToken() ? '/compose' : '/login';
        $this->navigate($destination)->transition(Transition::SlideFromBottom);
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

    public function openProfile(int $postId): void
    {
        $post = collect($this->posts)->firstWhere('id', $postId);
        $userId = is_array($post) ? ($post['author']['id'] ?? null) : null;

        if (is_int($userId)) {
            $this->navigate("/people/{$userId}")->transition(Transition::SlideFromRight);
        }
    }

    public function toggleReaction(int $id): void
    {
        $this->runInteraction($id, 'reaction');
    }

    public function toggleBookmark(int $id): void
    {
        $this->runInteraction($id, 'bookmark');
    }

    public function toggleRepost(int $id): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        try {
            $result = $this->api()->toggleRepost($id);
            $index = array_search($id, array_column($this->posts, 'id'), true);

            if (is_int($index)) {
                $this->posts[$index]['is_reposted'] = $result['active'];
                $this->posts[$index]['counts']['reposts'] = $result['count'];
            }
        } catch (Throwable) {
            $this->error = 'That repost did not complete. Please try again.';
        }
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, NavAction>
     */
    public function postMenu(array $post): array
    {
        $postId = (int) $post['id'];
        $permissions = is_array($post['permissions'] ?? null) ? $post['permissions'] : [];
        $actions = [
            NavAction::make("conversation-{$postId}")
                ->label('View conversation')
                ->icon(ios: Ios::BubbleLeft, android: Android::Comment)
                ->press("openPost({$postId})"),
            NavAction::make("repost-{$postId}")
                ->label(($post['is_reposted'] ?? false) ? 'Undo repost' : 'Repost')
                ->icon(ios: Ios::Arrow2Squarepath, android: Android::Repeat)
                ->press("toggleRepost({$postId})"),
        ];

        if ($permissions['update'] ?? false) {
            $actions[] = NavAction::divider();
            $actions[] = NavAction::make("edit-{$postId}")
                ->label('Edit post')
                ->icon(ios: Ios::Pencil, android: Android::Edit)
                ->press("editPost({$postId})");
        }

        if ($permissions['moderate'] ?? false) {
            $actions[] = NavAction::make("publish-{$postId}")
                ->label('Publish')
                ->icon(ios: Ios::CheckmarkCircle, android: Android::CheckCircle)
                ->press("publishPost({$postId})");
            $actions[] = NavAction::make("reject-{$postId}")
                ->label('Reject')
                ->icon(ios: Ios::XmarkCircle, android: Android::Cancel)
                ->press("rejectPost({$postId})");
        }

        if ($permissions['delete'] ?? false) {
            $actions[] = NavAction::make("delete-{$postId}")
                ->label('Delete post')
                ->icon(ios: Ios::Trash, android: Android::Delete)
                ->destructive()
                ->press("confirmDeletePost({$postId})");
        }

        return $actions;
    }

    public function editPost(int $postId): void
    {
        $this->navigate("/posts/{$postId}/edit")->transition(Transition::SlideFromRight);
    }

    public function publishPost(int $postId): void
    {
        $this->moderatePost($postId, 'published');
    }

    public function rejectPost(int $postId): void
    {
        $this->moderatePost($postId, 'rejected');
    }

    public function confirmDeletePost(int $postId): void
    {
        $alert = Dialog::alert(
            'Delete this post?',
            'This permanently removes it and its replies from Laraloom.',
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
            $this->posts = array_values(array_filter(
                $this->posts,
                fn (array $post): bool => (int) $post['id'] !== $postId,
            ));
        } catch (Throwable) {
            $this->error = 'That post could not be deleted.';
        }
    }

    public function render(): View
    {
        return view('native.laraloom.today');
    }

    private function runInteraction(int $id, string $type): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        try {
            $result = $type === 'reaction'
                ? $this->api()->toggleReaction($id)
                : $this->api()->toggleBookmark($id);
            $index = array_search($id, array_column($this->posts, 'id'), true);

            if (is_int($index)) {
                $stateKey = $type === 'reaction' ? 'is_reacted' : 'is_bookmarked';
                $this->posts[$index][$stateKey] = $result['active'];
                $countKey = $type === 'reaction' ? 'reactions' : 'bookmarks';
                $this->posts[$index]['counts'][$countKey] = $result['count'];
            }
        } catch (Throwable) {
            $this->error = 'That action did not complete. Please try again.';
        }
    }

    private function moderatePost(int $postId, string $status): void
    {
        try {
            $updated = $this->api()->moderatePost($postId, $status);
            $index = array_search($postId, array_column($this->posts, 'id'), true);

            if (is_int($index)) {
                $this->posts[$index] = $updated;
            }
        } catch (Throwable) {
            $this->error = 'That moderation action did not complete.';
        }
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
