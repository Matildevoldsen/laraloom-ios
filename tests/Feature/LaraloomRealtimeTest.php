<?php

use App\Contracts\TokenStore;
use App\Services\LaraloomRealtime;
use Native\Mobile\Testing\FakeBridge;

function realtimeTokenStore(?string $token = null): TokenStore
{
    return new class($token) implements TokenStore
    {
        public function __construct(private ?string $token) {}

        public function get(): ?string
        {
            return $this->token;
        }

        public function put(string $token): void
        {
            $this->token = $token;
        }

        public function forget(): void
        {
            $this->token = null;
        }
    };
}

it('ships with the public Laraloom WebSocket key', function () {
    expect(config('laraloom.realtime.key'))->toBe('laraloom-b2yknl');
});

it('connects and subscribes to the public feed', function () {
    config()->set('laraloom.realtime', [
        'key' => 'public-key',
        'host' => 'wss.vask.dev',
        'port' => 443,
    ]);
    $bridge = FakeBridge::enable()
        ->respondTo('WebSockets.Connect', ['success' => true])
        ->respondTo('WebSockets.Subscribe', ['success' => true]);

    (new LaraloomRealtime(realtimeTokenStore()))->subscribeToFeed();

    $bridge->assertCallOrder(['WebSockets.Connect', 'WebSockets.Subscribe'])
        ->assertCalled('WebSockets.Subscribe', fn (array $parameters): bool => $parameters === [
            'channel' => 'laraloom.feed',
            'events' => ['community.activity'],
        ]);
});

it('subscribes to realtime follow changes for a profile', function () {
    config()->set('laraloom.realtime', [
        'key' => 'public-key',
        'host' => 'wss.vask.dev',
        'port' => 443,
    ]);
    $bridge = FakeBridge::enable()
        ->respondTo('WebSockets.Connect', ['success' => true])
        ->respondTo('WebSockets.Subscribe', ['success' => true]);

    (new LaraloomRealtime(realtimeTokenStore('secure-token')))->subscribeToProfile(17);

    $bridge->assertCalled('WebSockets.Subscribe', fn (array $parameters): bool => $parameters === [
        'channel' => 'laraloom.profiles.17',
        'events' => ['follow.changed'],
    ]);
});

it('authenticates the private admin subscription with secure storage token', function () {
    config()->set('laraloom.api_url', 'https://laraloom.example/api/v1');
    config()->set('laraloom.realtime', [
        'key' => 'public-key',
        'host' => 'wss.vask.dev',
        'port' => 443,
    ]);
    $bridge = FakeBridge::enable()
        ->respondTo('WebSockets.Connect', ['success' => true])
        ->respondTo('WebSockets.Subscribe', ['success' => true]);

    (new LaraloomRealtime(realtimeTokenStore('secure-token')))->subscribeToAdmin();

    $bridge->assertCalled('WebSockets.Connect', fn (array $parameters): bool => $parameters['authEndpoint'] === 'https://laraloom.example/api/v1/broadcasting/auth'
        && $parameters['authToken'] === 'secure-token')
        ->assertCalled('WebSockets.Subscribe', fn (array $parameters): bool => $parameters['channel'] === 'private-laraloom.admin');
});

it('stays disconnected until a public key is configured', function () {
    config()->set('laraloom.realtime.key', null);
    $bridge = FakeBridge::enable();

    (new LaraloomRealtime(realtimeTokenStore()))->subscribeToFeed();

    $bridge->assertNotCalled('WebSockets.Connect')->assertNotCalled('WebSockets.Subscribe');
});
