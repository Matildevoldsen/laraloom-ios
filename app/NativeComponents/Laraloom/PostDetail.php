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
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Browser;
use Native\Mobile\Facades\Dialog;
use Throwable;

class PostDetail extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    /** @var array<string, mixed> */
    public array $post = [];

    /** @var array<int, array<string, mixed>> */
    public array $comments = [];

    public string $replyBody = '';

    public ?int $parentId = null;

    public string $replyingTo = '';

    public bool $isSubmitting = false;

    public string $error = '';

    public function mount(): void
    {
        $this->refreshConversation();
        app(LaraloomRealtime::class)->subscribeToPost((int) $this->param('id'));
    }

    #[On(MessageReceived::class)]
    public function handleRealtimeActivity(string $event): void
    {
        if ($event === LaraloomRealtime::ActivityEvent) {
            $this->refreshConversation();
        }
    }

    public function navTitle(): string
    {
        return 'Post';
    }

    public function openProfile(?int $commentId = null): void
    {
        $comment = $commentId === null ? null : collect($this->comments)->firstWhere('id', $commentId);
        $userId = is_array($comment)
            ? ($comment['author']['id'] ?? null)
            : ($this->post['author']['id'] ?? null);

        if (is_int($userId)) {
            $this->navigate("/people/{$userId}")->transition(Transition::SlideFromRight);
        }
    }

    public function replyTo(int $commentId): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        $comment = collect($this->comments)->firstWhere('id', $commentId);
        $this->parentId = $commentId;
        $this->replyingTo = is_array($comment)
            ? (string) ($comment['author']['username'] ?? $comment['author']['name'] ?? '')
            : '';
    }

    public function cancelReply(): void
    {
        $this->parentId = null;
        $this->replyingTo = '';
    }

    public function submitReply(): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        if (trim($this->replyBody) === '') {
            $this->error = 'Write a reply first.';

            return;
        }

        $this->isSubmitting = true;

        try {
            $this->api()->createComment((int) $this->post['id'], trim($this->replyBody), $this->parentId);
            $this->replyBody = '';
            $this->cancelReply();
            $this->refreshConversation();
        } catch (Throwable) {
            $this->error = 'Your reply could not be posted.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function toggleReaction(): void
    {
        $this->toggleInteraction('reaction');
    }

    public function toggleBookmark(): void
    {
        $this->toggleInteraction('bookmark');
    }

    public function toggleRepost(): void
    {
        $this->toggleInteraction('repost');
    }

    public function confirmDeleteComment(int $commentId): void
    {
        $alert = Dialog::alert(
            'Delete this reply?',
            'This removes it from the conversation.',
            ['Cancel', 'Delete'],
        );
        $alert->buttonPressed(function (ButtonPressed $event) use ($commentId): void {
            if ($event->label === 'Delete') {
                $this->deleteComment($commentId);
            }
        });
        $alert->show();
    }

    public function deleteComment(int $commentId): void
    {
        try {
            $this->api()->deleteComment($commentId);
            $this->refreshConversation();
        } catch (Throwable) {
            $this->error = 'That reply could not be deleted.';
        }
    }

    /**
     * @param  array<string, mixed>  $comment
     * @return array<int, NavAction>
     */
    public function commentMenu(array $comment): array
    {
        $commentId = (int) $comment['id'];
        $actions = [
            NavAction::make("reply-{$commentId}")
                ->label('Reply')
                ->icon(ios: Ios::BubbleLeft, android: Android::Comment)
                ->press("replyTo({$commentId})"),
        ];

        if ($comment['permissions']['delete'] ?? false) {
            $actions[] = NavAction::divider();
            $actions[] = NavAction::make("delete-comment-{$commentId}")
                ->label('Delete reply')
                ->icon(ios: Ios::Trash, android: Android::Delete)
                ->destructive()
                ->press("confirmDeleteComment({$commentId})");
        }

        return $actions;
    }

    public function openSource(): void
    {
        $url = $this->post['url'] ?? null;

        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            Browser::inApp($url);
        }
    }

    public function openMedia(int $attachmentIndex): void
    {
        $url = $this->post['attachments'][$attachmentIndex]['url'] ?? null;

        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            Browser::inApp($url);
        }
    }

    public function render(): View
    {
        return view('native.laraloom.post-detail');
    }

    private function refreshConversation(): void
    {
        try {
            $postId = (int) $this->param('id');
            $this->post = $this->api()->post($postId);
            $this->comments = $this->api()->comments($postId)['data'];
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'This conversation could not be opened.';
        }
    }

    private function toggleInteraction(string $type): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        try {
            $result = match ($type) {
                'reaction' => $this->api()->toggleReaction((int) $this->post['id']),
                'bookmark' => $this->api()->toggleBookmark((int) $this->post['id']),
                default => $this->api()->toggleRepost((int) $this->post['id']),
            };
            $state = match ($type) {
                'reaction' => 'is_reacted',
                'bookmark' => 'is_bookmarked',
                default => 'is_reposted',
            };
            $count = match ($type) {
                'reaction' => 'reactions',
                'bookmark' => 'bookmarks',
                default => 'reposts',
            };
            $this->post[$state] = $result['active'];
            $this->post['counts'][$count] = $result['count'];
        } catch (Throwable) {
            $this->error = 'That action did not complete.';
        }
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
