<?php

use App\NativeComponents\Laraloom\Projects;
use App\NativeComponents\Laraloom\Today;
use App\Services\LaraloomApiClient;

test('the app boots directly into the native Laraloom feed', function () {
    $this->get('/')->assertOk();

    expect(route('today'))->toContain('/');
});

test('today loads feed data into native component state', function () {
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('feed')->once()->andReturn([
        'data' => [['id' => 1, 'title' => 'Laravel 13 shipped']],
        'meta' => [],
    ]);
    app()->instance(LaraloomApiClient::class, $api);

    $component = new Today;
    $component->mount();

    expect($component->posts)->toHaveCount(1)
        ->and($component->error)->toBe('');
});

test('projects exposes a retryable error instead of fake content', function () {
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('projects')->once()->andThrow(new RuntimeException('offline'));
    app()->instance(LaraloomApiClient::class, $api);

    $component = new Projects;
    $component->mount();

    expect($component->projects)->toBe([])
        ->and($component->error)->not->toBe('');
});

test('the iOS privacy manifest declares runtime required reason APIs', function () {
    $manifest = file_get_contents(resource_path('ios/PrivacyInfo.xcprivacy'));

    expect($manifest)
        ->toContain('NSPrivacyAccessedAPICategoryFileTimestamp')
        ->toContain('C617.1')
        ->toContain('NSPrivacyAccessedAPICategorySystemBootTime')
        ->toContain('35F9.1')
        ->toContain('NSPrivacyAccessedAPICategoryDiskSpace')
        ->toContain('E174.1');
});
