<?php

namespace App\Contracts;

interface TokenStore
{
    public function get(): ?string;

    public function put(string $token): void;

    public function forget(): void;
}
