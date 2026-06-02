<?php

declare(strict_types=1);

namespace App\OAuth\Data;

use App\Support\Data\Input;

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
            provider: Input::string($data, 'provider'),
            providerId: Input::string($data, 'provider_id'),
            name: Input::string($data, 'name'),
            email: Input::nullableString($data, 'email'),
            avatarUrl: Input::nullableString($data, 'avatar_url'),
        );
    }
}
