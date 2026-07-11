<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenStore;
use Matildevoldsen\NativeWebSockets\Facades\WebSockets;

final class LaraloomRealtime
{
    public const string ActivityEvent = 'community.activity';

    public function __construct(private readonly TokenStore $tokens) {}

    public function subscribeToFeed(): void
    {
        if ($this->connect()) {
            WebSockets::subscribe('laraloom.feed', [self::ActivityEvent]);
        }
    }

    public function subscribeToPost(int $postId): void
    {
        if ($this->connect()) {
            WebSockets::subscribe("laraloom.posts.{$postId}", [self::ActivityEvent]);
        }
    }

    public function subscribeToAdmin(): void
    {
        if ($this->connect()) {
            WebSockets::subscribe('private-laraloom.admin', [self::ActivityEvent]);
        }
    }

    private function connect(): bool
    {
        $key = config('laraloom.realtime.key');

        if (! is_string($key) || $key === '') {
            return false;
        }

        $token = $this->tokens->get();
        WebSockets::connect(
            key: $key,
            host: (string) config('laraloom.realtime.host', 'wss.vask.dev'),
            port: (int) config('laraloom.realtime.port', 443),
            authEndpoint: $token === null
                ? null
                : rtrim((string) config('laraloom.api_url'), '/').'/broadcasting/auth',
            authToken: $token,
        );

        return true;
    }
}
