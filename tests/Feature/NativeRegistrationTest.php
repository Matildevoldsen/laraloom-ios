<?php

use App\NativeComponents\Laraloom\Register;
use App\Services\LaraloomApiClient;
use Native\Mobile\Testing\Native;

it('registers with normalized native form values', function () {
    $api = Mockery::mock(LaraloomApiClient::class);
    $api->shouldReceive('register')
        ->once()
        ->with(
            'Native Builder',
            'native_builder',
            'native@example.com',
            'Secure-password-1!',
            'Secure-password-1!',
            'Laraloom mobile',
        )
        ->andReturn(['id' => 8, 'username' => 'native_builder']);
    app()->instance(LaraloomApiClient::class, $api);

    Native::test(Register::class, platform: 'ios')
        ->input('name', ' Native Builder ')
        ->input('username', ' NATIVE_BUILDER ')
        ->input('email', ' NATIVE@EXAMPLE.COM ')
        ->input('password', 'Secure-password-1!')
        ->input('passwordConfirmation', 'Secure-password-1!')
        ->tap('Create secure account')
        ->assertReplacedWith('/profile');
});
