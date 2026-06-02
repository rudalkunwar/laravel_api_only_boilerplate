<?php

declare(strict_types=1);

namespace App\Auth\Data;

use App\Support\Data\Input;

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
            email: Input::string($data, 'email'),
            token: Input::string($data, 'token'),
            password: Input::string($data, 'password'),
        );
    }
}
