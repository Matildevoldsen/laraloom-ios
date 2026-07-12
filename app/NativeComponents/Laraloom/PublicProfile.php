<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use App\Services\LaraloomRealtime;
use Illuminate\View\View;
use Matildevoldsen\NativeWebSockets\Events\MessageReceived;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Native\Mobile\Facades\Dialog;
use Throwable;

class PublicProfile extends NativeComponent
{
    /** @var array<string, mixed> */
    public array $profile = [];

    public int $userId = 0;

    public int $selectedTab = 0;

    public ?string $peopleList = null;

    public bool $showPeopleModal = false;

    public string $error = '';

    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    public function mount(): void
    {
        $this->userId = (int) $this->param('id');
        $this->refresh();

        if (isset($this->profile['id'])) {
            app(LaraloomRealtime::class)->subscribeToProfile((int) $this->profile['id']);
        }
    }

    public function navTitle(): string
    {
        return $this->profile['name'] ?? 'Profile';
    }

    public function refresh(): void
    {
        try {
            $this->profile = $this->api()->profileById($this->userId);
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'This profile could not be refreshed.';
        }
    }

    #[On(MessageReceived::class)]
    public function handleRealtimeFollow(string $event): void
    {
        if ($event === LaraloomRealtime::FollowEvent) {
            $this->refresh();
        }
    }

    public function selectTab(int $index): void
    {
        $this->selectedTab = $index;
    }

    public function showPeople(string $relationship): void
    {
        if (in_array($relationship, ['followers', 'following'], true)) {
            $this->peopleList = $relationship;
            $this->showPeopleModal = true;
        }
    }

    public function closePeople(): void
    {
        $this->peopleList = null;
        $this->showPeopleModal = false;
    }

    /** @return array<int, array<string, mixed>> */
    public function visiblePeople(): array
    {
        if ($this->peopleList === null) {
            return [];
        }

        $people = $this->profile[$this->peopleList] ?? [];

        return is_array($people) ? $people : [];
    }

    public function toggleFollow(): void
    {
        if (! $this->api()->hasToken()) {
            $this->navigate('/login')->transition(Transition::SlideFromBottom);

            return;
        }

        try {
            $result = $this->api()->toggleFollow((string) $this->profile['username']);
            $this->profile['is_following'] = $result['active'];
            $this->profile['counts']['followers'] = $result['count'];
            $this->error = '';
            Dialog::toast($result['active'] ? 'Followed' : 'Unfollowed');
        } catch (Throwable) {
            $this->error = 'That follow did not complete. Please try again.';
        }
    }

    public function openPost(int $id): void
    {
        $this->navigate("/posts/{$id}")->transition(Transition::SlideFromRight);
    }

    public function openProject(string $slug): void
    {
        $this->navigate("/projects/{$slug}")->transition(Transition::SlideFromRight);
    }

    public function openPerson(int $userId): void
    {
        $this->navigate("/people/{$userId}")->transition(Transition::SlideFromRight);
    }

    public function render(): View
    {
        return view('native.laraloom.public-profile');
    }

    private function api(): LaraloomApiClient
    {
        return app(LaraloomApiClient::class);
    }
}
