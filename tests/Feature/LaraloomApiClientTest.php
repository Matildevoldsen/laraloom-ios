<?php

use App\Contracts\TokenStore;
use App\Services\LaraloomApiClient;
use App\Services\SecureTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Facades\SecureStorage;

beforeEach(function () {
    Http::preventStrayRequests();
    config()->set('laraloom.api_url', 'https://laraloom.test/api/v1');
});

function tokenStore(?string $initialToken = null): TokenStore
{
    return new class($initialToken) implements TokenStore
    {
        public function __construct(public ?string $token) {}

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

test('it fetches the typed feed envelope', function () {
    Http::fake([
        'laraloom.test/api/v1/feed*' => Http::response([
            'data' => [['id' => 1, 'title' => 'Native Laravel']],
            'meta' => ['next_cursor' => 'next-page'],
        ]),
    ]);

    $result = (new LaraloomApiClient(tokenStore()))->feed(kind: 'article');

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0]['title'])->toBe('Native Laravel');
    Http::assertSent(fn ($request): bool => $request['kind'] === 'article');
});

test('search sends its query to the production-shaped API endpoint', function () {
    Http::fake([
        'laraloom.test/api/v1/feed*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    (new LaraloomApiClient(tokenStore()))->searchFeed('native php');

    Http::assertSent(fn ($request): bool => $request['q'] === 'native php');
});

test('it stores the scoped mobile token after login', function () {
    Http::fake([
        'laraloom.test/api/v1/auth/token' => Http::response([
            'token' => '1|secure-token',
            'user' => ['id' => 7, 'name' => 'Matilde'],
        ]),
    ]);
    $tokens = tokenStore();

    $user = (new LaraloomApiClient($tokens))->login('matilde@example.com', 'secret', 'iPhone');

    expect($tokens->get())->toBe('1|secure-token')
        ->and($user['name'])->toBe('Matilde');
});

test('registration stores the scoped token returned by the API', function () {
    Http::fake([
        'laraloom.test/api/v1/auth/register' => Http::response([
            'token' => '2|registered-token',
            'user' => ['id' => 8, 'username' => 'nativebuilder'],
        ], 201),
    ]);
    $tokens = tokenStore();

    $user = (new LaraloomApiClient($tokens))->register(
        'Native Builder',
        'nativebuilder',
        'native@example.com',
        'secure-password',
        'secure-password',
        'Laraloom mobile',
    );

    expect($tokens->get())->toBe('2|registered-token')
        ->and($user['username'])->toBe('nativebuilder');
});

test('authenticated requests require and send the stored bearer token', function () {
    Http::fake([
        'laraloom.test/api/v1/me' => Http::response(['data' => ['id' => 7]]),
    ]);

    (new LaraloomApiClient(tokenStore('1|secure-token')))->me();

    Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer 1|secure-token'));
});

test('community posts are published with the keychain token', function () {
    Http::fake([
        'laraloom.test/api/v1/posts' => Http::response([
            'data' => ['id' => 42, 'kind' => 'note'],
        ], 201),
    ]);

    $post = (new LaraloomApiClient(tokenStore('1|secure-token')))->createPost([
        'kind' => 'note',
        'body' => 'Shipped from iPhone.',
    ]);

    expect($post['id'])->toBe(42);
    Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer 1|secure-token')
        && $request['body'] === 'Shipped from iPhone.');
});

test('members can list update and delete their posts through authenticated endpoints', function () {
    Http::fake([
        'laraloom.test/api/v1/me/posts' => Http::response([
            'data' => [['id' => 42, 'body' => 'Before']],
            'meta' => [],
        ]),
        'laraloom.test/api/v1/posts/42' => Http::sequence()
            ->push(['data' => ['id' => 42, 'body' => 'After']])
            ->push([], 204),
    ]);
    $client = new LaraloomApiClient(tokenStore('1|secure-token'));

    $posts = $client->myPosts();
    $post = $client->updatePost(42, ['kind' => 'note', 'body' => 'After']);
    $client->deletePost(42);

    expect($posts['data'][0]['id'])->toBe(42)
        ->and($post['body'])->toBe('After');
    Http::assertSentCount(3);
});

test('admin moderation uses protected API endpoints', function () {
    Http::fake([
        'laraloom.test/api/v1/admin' => Http::response([
            'data' => ['counts' => ['pending_posts' => 1], 'posts' => []],
        ]),
        'laraloom.test/api/v1/admin/posts/42/status' => Http::response([
            'data' => ['id' => 42, 'status' => 'published'],
        ]),
    ]);
    $client = new LaraloomApiClient(tokenStore('1|admin-token'));

    $dashboard = $client->adminDashboard();
    $post = $client->moderatePost(42, 'published');

    expect($dashboard['counts']['pending_posts'])->toBe(1)
        ->and($post['status'])->toBe('published');
});

test('conversations and reposts use authenticated social endpoints', function () {
    Http::fake([
        'laraloom.test/api/v1/posts/42/comments' => Http::sequence()
            ->push(['data' => [['id' => 8, 'body' => 'First reply']], 'meta' => []])
            ->push(['data' => ['id' => 9, 'body' => 'Nested reply', 'parent_id' => 8]], 201),
        'laraloom.test/api/v1/comments/9' => Http::response(status: 204),
        'laraloom.test/api/v1/posts/42/repost' => Http::response(['active' => true, 'count' => 3]),
    ]);
    $client = new LaraloomApiClient(tokenStore('1|secure-token'));

    $comments = $client->comments(42);
    $reply = $client->createComment(42, 'Nested reply', 8);
    $repost = $client->toggleRepost(42);
    $client->deleteComment(9);

    expect($comments['data'][0]['id'])->toBe(8)
        ->and($reply['parent_id'])->toBe(8)
        ->and($repost)->toBe(['active' => true, 'count' => 3]);
    Http::assertSent(fn ($request): bool => $request->url() === 'https://laraloom.test/api/v1/posts/42/comments'
        && $request->method() === 'POST'
        && $request['parent_id'] === 8
        && $request->hasHeader('Authorization', 'Bearer 1|secure-token'));
});

test('logout revokes the server token before clearing keychain state', function () {
    Http::fake([
        'laraloom.test/api/v1/auth/token' => Http::response(status: 204),
    ]);
    $tokens = tokenStore('1|secure-token');

    (new LaraloomApiClient($tokens))->logout();

    expect($tokens->get())->toBeNull();
});

test('account deletion verifies the password before clearing secure storage', function () {
    Http::fake([
        'laraloom.test/api/v1/me' => Http::response(status: 204),
    ]);
    $tokens = tokenStore('1|secure-token');

    (new LaraloomApiClient($tokens))->deleteAccount('correct-password');

    expect($tokens->get())->toBeNull();
    Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
        && $request['password'] === 'correct-password'
        && $request->hasHeader('Authorization', 'Bearer 1|secure-token'));
});

test('the token adapter uses the NativePHP PHP secure storage contract', function () {
    SecureStorage::shouldReceive('get')
        ->once()
        ->with('laraloom.auth_token')
        ->andReturn('1|native-keychain-token');
    SecureStorage::shouldReceive('set')
        ->once()
        ->with('laraloom.auth_token', '2|replacement-token')
        ->andReturnTrue();
    SecureStorage::shouldReceive('delete')
        ->once()
        ->with('laraloom.auth_token')
        ->andReturnTrue();

    $tokens = new SecureTokenStore;

    expect($tokens->get())->toBe('1|native-keychain-token');
    $tokens->put('2|replacement-token');
    $tokens->forget();
});
