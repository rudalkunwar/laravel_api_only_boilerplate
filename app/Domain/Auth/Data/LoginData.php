<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

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
        $deviceName = $data['device_name'] ?? null;

        return new self(
            email: strtolower((string) $data['email']),
            password: (string) $data['password'],
            deviceName: is_string($deviceName) && $deviceName !== '' ? $deviceName : 'api',
        );
    }
}
