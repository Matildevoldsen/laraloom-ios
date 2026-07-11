<?php

namespace App\NativeComponents\Laraloom;

use App\Services\LaraloomApiClient;
use Illuminate\View\View;
use Native\Mobile\Edge\Layouts\Builders\TabBarOptions;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Browser;
use Throwable;

class ProjectDetail extends NativeComponent
{
    public function tabBarOptions(): ?TabBarOptions
    {
        return TabBarOptions::make()->hidden();
    }

    /** @var array<string, mixed> */
    public array $project = [];

    public string $error = '';

    public function mount(): void
    {
        try {
            $this->project = app(LaraloomApiClient::class)->project((string) $this->param('slug'));
        } catch (Throwable) {
            $this->error = 'This project could not be opened.';
        }
    }

    public function navTitle(): string
    {
        return (string) ($this->project['name'] ?? 'Project');
    }

    public function openWebsite(): void
    {
        $this->open('url');
    }

    public function openRepository(): void
    {
        $this->open('repository_url');
    }

    public function render(): View
    {
        return view('native.laraloom.project-detail');
    }

    private function open(string $key): void
    {
        $url = $this->project[$key] ?? null;

        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            Browser::inApp($url);
        }
    }
}
