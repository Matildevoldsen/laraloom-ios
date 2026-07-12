<?php

use App\NativeComponents\Laraloom\PostDetail;
use App\NativeComponents\Laraloom\Today;
use App\Services\LaraloomApiClient;
use Native\Mobile\Testing\Native;

function profileNavigationPost(): array
{
    return [
        'id' => 7,
        'kind' => 'note',
        'title' => 'A useful post',
        'body' => 'Built with Laravel.',
        'summary' => '',
        'url' => null,
        'tags' => [],
        'is_ai_curated' => false,
        'is_reacted' => false,
        'is_reposted' => false,
        'is_bookmarked' => false,
        'author' => ['id' => 3, 'name' => 'Matilde', 'username' => 'matilde'],
        'source' => ['name' => 'Community', 'author' => null],
        'counts' => ['comments' => 0, 'reposts' => 0, 'reactions' => 0],
    ];
}

it('opens an author profile from a feed card', function () {
    config()->set('laraloom.realtime.key', null);
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('feed')->once()->andReturn(['data' => [profileNavigationPost()], 'meta' => []]);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(Today::class, platform: 'ios')
        ->tap('Matilde')
        ->assertNavigatedTo('/people/3');
});

it('opens an author profile from a conversation', function () {
    config()->set('laraloom.realtime.key', null);
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('post')->once()->with(7)->andReturn(profileNavigationPost());
    $api->shouldReceive('comments')->once()->with(7)->andReturn(['data' => [], 'meta' => []]);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(PostDetail::class, ['id' => '7'], platform: 'ios')
        ->tap('Matilde')
        ->assertNavigatedTo('/people/3');
});
