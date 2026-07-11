<?php

use App\NativeComponents\Laraloom\Login;
use App\Services\LaraloomApiClient;
use Native\Mobile\Testing\Native;

it('syncs native credentials and signs in through the API client', function () {
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('login')
        ->once()
        ->with('member@example.com', 'correct-password', 'Laraloom for iPhone')
        ->andReturn(['id' => 7, 'name' => 'Member']);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(Login::class, platform: 'ios')
        ->assertElement('outlined_text_input', fn (array $node): bool => ($node['ref'] ?? null) === 'login-email'
            && ($node['props']['sync_mode'] ?? null) === 'debounce'
            && ($node['props']['debounce_ms'] ?? null) === 400)
        ->assertElement('outlined_text_input', fn (array $node): bool => ($node['ref'] ?? null) === 'login-password'
            && ($node['props']['sync_mode'] ?? null) === 'debounce'
            && ($node['props']['debounce_ms'] ?? null) === 400)
        ->input('email', 'member@example.com')
        ->assertSet('email', 'member@example.com')
        ->input('password', 'correct-password')
        ->assertSet('password', 'correct-password')
        ->tap('Sign in')
        ->assertReplacedWith('/profile');
});

it('normalizes email addresses before authentication', function () {
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('login')
        ->once()
        ->with('member@example.com', 'correct-password', 'Laraloom for iPhone')
        ->andReturn(['id' => 7, 'name' => 'Member']);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(Login::class, platform: 'ios')
        ->input('email', ' MEMBER@EXAMPLE.COM ')
        ->input('password', 'correct-password')
        ->tap('Sign in')
        ->assertReplacedWith('/profile');
});
