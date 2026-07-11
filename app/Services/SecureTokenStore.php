<?php

namespace App\Services;

use App\Contracts\TokenStore;
use Native\Mobile\Facades\SecureStorage;
use RuntimeException;

class SecureTokenStore implements TokenStore
{
    private const string TokenKey = 'laraloom.auth_token';

    public function get(): ?string
    {
        $token = SecureStorage::get(self::TokenKey);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function put(string $token): void
    {
        if (! SecureStorage::set(self::TokenKey, $token)) {
            throw new RuntimeException('The authentication token could not be stored securely.');
        }
    }

    public function forget(): void
    {
        if (! SecureStorage::delete(self::TokenKey)) {
            throw new RuntimeException('The authentication token could not be removed from secure storage.');
        }
    }
}
