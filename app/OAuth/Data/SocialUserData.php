<?php

declare(strict_types=1);

namespace App\OAuth\Data;

final readonly class SocialUserData
{
    public function __construct(
        public string $provider,
        public string $providerId,
        public string $name,
        public ?string $email = null,
        public ?string $avatarUrl = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            provider: (string) ($data['provider'] ?? ''),
            providerId: (string) ($data['provider_id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            email: isset($data['email']) && $data['email'] !== '' ? (string) $data['email'] : null,
            avatarUrl: isset($data['avatar_url']) ? (string) $data['avatar_url'] : null,
        );
    }
}
