<?php

declare(strict_types=1);

namespace App\Auth\Data;

use App\Support\Data\Input;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: strtolower(Input::string($data, 'email')),
            password: Input::string($data, 'password'),
            deviceName: Input::nullableString($data, 'device_name') ?? 'api',
        );
    }
}
