<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

use App\Domain\User\Models\User;

final readonly class AuthTokenData
{
    public function __construct(
        public User $user,
        public string $token,
        public string $tokenType = 'Bearer',
    ) {}
}
