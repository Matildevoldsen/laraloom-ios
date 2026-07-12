<?php

namespace App\Services;

use App\Contracts\TokenStore;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LaraloomApiClient
{
    public function __construct(private readonly TokenStore $tokens) {}

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function feed(?string $cursor = null, ?string $kind = null): array
    {
        return $this->collection('/feed', array_filter([
            'cursor' => $cursor,
            'kind' => $kind,
        ]), $this->hasToken());
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function searchFeed(string $query): array
    {
        return $this->collection('/feed', ['q' => $query], $this->hasToken());
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function followingFeed(?string $cursor = null): array
    {
        return $this->collection('/feed/following', array_filter(['cursor' => $cursor]), true);
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function projects(?string $cursor = null): array
    {
        return $this->collection('/projects', array_filter(['cursor' => $cursor]));
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function searchProjects(string $query): array
    {
        return $this->collection('/projects', ['q' => $query]);
    }

    /** @return array<string, mixed> */
    public function post(int $id): array
    {
        return $this->data($this->request($this->hasToken())->get("/posts/{$id}"));
    }

    /** @return array<string, mixed> */
    public function project(string $slug): array
    {
        return $this->data($this->request()->get("/projects/{$slug}"));
    }

    /** @return array<string, mixed> */
    public function profile(string $username): array
    {
        return $this->data($this->request($this->hasToken())->get("/profiles/{$username}"));
    }

    /** @return array<string, mixed> */
    public function profileById(int $userId): array
    {
        return $this->data($this->request($this->hasToken())->get("/profiles/id/{$userId}"));
    }

    /** @return array{active: bool, count: int} */
    public function toggleFollow(string $username): array
    {
        return $this->interaction("/profiles/{$username}/follow");
    }

    /** @return array<string, mixed> */
    public function me(): array
    {
        return $this->data($this->request(authenticated: true)->get('/me'));
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function myPosts(): array
    {
        return $this->collection('/me/posts', [], true);
    }

    /** @return array<string, mixed> */
    public function login(string $email, string $password, string $deviceName): array
    {
        $response = $this->request()->post('/auth/token', [
            'email' => $email,
            'password' => $password,
            'device_name' => $deviceName,
        ])->throw();

        return $this->storeAuthentication($response);
    }

    /** @return array<string, mixed> */
    public function register(
        string $name,
        string $username,
        string $email,
        string $password,
        string $passwordConfirmation,
        string $deviceName,
    ): array {
        $response = $this->request()->post('/auth/register', [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'device_name' => $deviceName,
        ])->throw();

        return $this->storeAuthentication($response);
    }

    public function logout(): void
    {
        $this->request(authenticated: true)->delete('/auth/token')->throw();
        $this->tokens->forget();
    }

    public function deleteAccount(string $password): void
    {
        $this->request(authenticated: true)->delete('/me', ['password' => $password])->throw();
        $this->tokens->forget();
    }

    /**
     * @param  array{kind: string, title?: string, body?: string, url?: string, tags?: string}  $attributes
     * @return array<string, mixed>
     */
    public function createPost(array $attributes): array
    {
        return $this->data(
            $this->request(authenticated: true)->post('/posts', $attributes),
        );
    }

    /**
     * @param  array{kind: string, title?: string, body?: string, url?: string, tags?: string}  $attributes
     * @return array<string, mixed>
     */
    public function updatePost(int $postId, array $attributes): array
    {
        return $this->data(
            $this->request(authenticated: true)->patch("/posts/{$postId}", $attributes),
        );
    }

    public function deletePost(int $postId): void
    {
        $this->request(authenticated: true)->delete("/posts/{$postId}")->throw();
    }

    /** @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>} */
    public function comments(int $postId): array
    {
        return $this->collection("/posts/{$postId}/comments", [], $this->hasToken());
    }

    /** @return array<string, mixed> */
    public function createComment(int $postId, string $body, ?int $parentId = null): array
    {
        return $this->data($this->request(authenticated: true)->post("/posts/{$postId}/comments", array_filter([
            'body' => $body,
            'parent_id' => $parentId,
        ], fn (mixed $value): bool => $value !== null)));
    }

    public function deleteComment(int $commentId): void
    {
        $this->request(authenticated: true)->delete("/comments/{$commentId}")->throw();
    }

    /** @return array<string, mixed> */
    public function adminDashboard(): array
    {
        return $this->data($this->request(authenticated: true)->get('/admin'));
    }

    /** @return array<string, mixed> */
    public function moderatePost(int $postId, string $status): array
    {
        return $this->data(
            $this->request(authenticated: true)->patch("/admin/posts/{$postId}/status", [
                'status' => $status,
            ]),
        );
    }

    public function hasToken(): bool
    {
        return $this->tokens->get() !== null;
    }

    /** @return array{active: bool, count: int} */
    public function toggleReaction(int $postId): array
    {
        return $this->interaction("/posts/{$postId}/reaction");
    }

    /** @return array{active: bool, count: int} */
    public function toggleBookmark(int $postId): array
    {
        return $this->interaction("/posts/{$postId}/bookmark");
    }

    /** @return array{active: bool, count: int} */
    public function toggleRepost(int $postId): array
    {
        return $this->interaction("/posts/{$postId}/repost");
    }

    /**
     * @param  array<string, string>  $query
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    private function collection(string $path, array $query, bool $authenticated = false): array
    {
        $response = $this->request($authenticated)->get($path, $query)->throw();

        return [
            'data' => $this->list($response->json('data')),
            'meta' => $this->array($response->json('meta')),
        ];
    }

    /** @return array<string, mixed> */
    private function data(Response $response): array
    {
        $response->throw();

        return $this->array($response->json('data'));
    }

    private function request(bool $authenticated = false): PendingRequest
    {
        $request = Http::baseUrl((string) config('laraloom.api_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->connectTimeout(5);

        if (! $authenticated) {
            return $request;
        }

        $token = $this->tokens->get();

        if ($token === null) {
            throw new \LogicException('Authentication is required for this request.');
        }

        return $request->withToken($token);
    }

    /** @return array{active: bool, count: int} */
    private function interaction(string $path): array
    {
        $response = $this->request(authenticated: true)->post($path)->throw();

        return [
            'active' => (bool) $response->json('active'),
            'count' => (int) $response->json('count'),
        ];
    }

    /** @return array<string, mixed> */
    private function storeAuthentication(Response $response): array
    {
        $token = $response->json('token');

        if (! is_string($token) || $token === '') {
            throw new \UnexpectedValueException('The Laraloom API returned an invalid token.');
        }

        $this->tokens->put($token);

        return $this->array($response->json('user'));
    }

    /** @return array<string, mixed> */
    private function array(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @return array<int, array<string, mixed>> */
    private function list(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_array(...)));
    }
}
