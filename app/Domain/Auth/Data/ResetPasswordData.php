<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

final readonly class ResetPasswordData
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            token: (string) $data['token'],
            password: (string) $data['password'],
        );
    }
}
