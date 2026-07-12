<?php

use App\NativeComponents\Laraloom\PublicProfile;
use App\Services\LaraloomApiClient;
use Native\Mobile\Testing\Native;

it('renders a public profile with posts packages and people', function () {
    config()->set('laraloom.realtime.key', null);
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('profileById')->once()->with(17)->andReturn([
        'id' => 17,
        'name' => 'Taylor Otwell',
        'username' => 'taylor',
        'headline' => 'Laravel creator',
        'bio' => 'Building tools for artisans.',
        'is_following' => false,
        'is_available_for_work' => false,
        'counts' => ['followers' => 100, 'following' => 10],
        'posts' => [['id' => 7, 'title' => 'Laravel 13', 'body' => 'Released.', 'summary' => '', 'counts' => []]],
        'projects' => [['slug' => 'laravel', 'name' => 'Laravel', 'kind' => 'package', 'tagline' => 'The PHP framework.']],
        'followers' => [['id' => 3, 'name' => 'Matilde', 'username' => 'matilde']],
        'following' => [],
    ]);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(PublicProfile::class, ['id' => '17'], platform: 'ios')
        ->assertSee('Taylor Otwell')
        ->assertSee('Posts')
        ->assertSee('Packages')
        ->assertSee('People');
});

it('changes follow state and shows native confirmation', function () {
    config()->set('laraloom.realtime.key', null);
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('profileById')->once()->with(17)->andReturn([
        'id' => 17,
        'name' => 'Taylor Otwell',
        'username' => 'taylor',
        'headline' => null,
        'bio' => null,
        'is_following' => false,
        'is_available_for_work' => false,
        'counts' => ['followers' => 100, 'following' => 10],
        'posts' => [],
        'projects' => [],
        'replies' => [],
        'reposted_posts' => [],
        'liked_posts' => [],
        'followers' => [],
        'following' => [],
    ]);
    $api->shouldReceive('hasToken')->once()->andReturnTrue();
    $api->shouldReceive('toggleFollow')->once()->with('taylor')->andReturn(['active' => true, 'count' => 101]);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(PublicProfile::class, ['id' => '17'], platform: 'ios')
        ->tap('Follow')
        ->assertSee('Unfollow')
        ->assertNativeCalled('Dialog.Toast', fn (array $parameters): bool => $parameters['message'] === 'Followed');
});
