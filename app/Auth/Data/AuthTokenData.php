<?php

declare(strict_types=1);

namespace App\Auth\Data;

final readonly class AuthTokenData
{
    public function __construct(
        public int $userId,
        public string $userName,
        public string $userEmail,
        public string $token,
        public string $tokenType = 'Bearer',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            userName: (string) $data['user_name'],
            userEmail: (string) $data['user_email'],
            token: (string) $data['token'],
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
        );
    }
}
