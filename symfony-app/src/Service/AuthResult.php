<?php

namespace App\Service;

final class AuthResult
{
    public function __construct(
        public readonly ?int    $userId,
        public readonly ?int    $tokenId,
        public readonly ?string $username,
    )
    {
    }

}
