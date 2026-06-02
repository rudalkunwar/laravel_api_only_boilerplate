<?php

declare(strict_types=1);

namespace App\Auth\Data;

use App\Support\Data\Input;

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
            userId: Input::integer($data, 'user_id'),
            userName: Input::string($data, 'user_name'),
            userEmail: Input::string($data, 'user_email'),
            token: Input::string($data, 'token'),
            tokenType: Input::string($data, 'token_type', 'Bearer'),
        );
    }
}
