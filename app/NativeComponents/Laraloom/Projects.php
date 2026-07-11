<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Edge\Transition;
use Throwable;

class Projects extends NativeComponent
{
    /** @var array<int, array<string, mixed>> */
    public array $projects = [];

    public string $error = '';

    public function mount(): void
    {
        $this->refresh();
    }

    public function navTitle(): string
    {
        return 'Built with Laravel';
    }

    public function showsNavBack(): bool
    {
        return false;
    }

    public function refresh(): void
    {
        try {
            $this->projects = app(LaraloomApiClient::class)->projects()['data'];
            $this->error = '';
        } catch (Throwable) {
            $this->error = 'Projects could not be loaded. Pull down to try again.';
        }
    }

    public function openProject(string $slug): void
    {
        $this->navigate("/projects/{$slug}")->transition(Transition::SlideFromRight);
    }

    /** @return array<int, array{title: string, subtitle: string, leading: string, url: string}> */
    public function onSearchQuery(string $query): array
    {
        if (trim($query) === '') {
            return [];
        }

        try {
            return array_map(
                fn (array $project): array => [
                    'title' => (string) $project['name'],
                    'subtitle' => (string) $project['tagline'],
                    'leading' => 'shippingbox',
                    'url' => '/projects/'.$project['slug'],
                ],
                app(LaraloomApiClient::class)->searchProjects($query)['data'],
            );
        } catch (Throwable) {
            return [];
        }
    }

    public function render(): View
    {
        return view('native.laraloom.projects');
    }
}
