<?php

declare(strict_types=1);

namespace App\User\Data;

use App\User\Models\User;

/**
 * Immutable representation of a user for transport between layers.
 */
final readonly class UserData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public bool $isVerified,
        public array $roles,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isVerified: $user->hasVerifiedEmail(),
            roles: array_values(array_filter($user->getRoleNames()->all(), 'is_string')),
        );
    }

    /**
     * @return array{id: int, name: string, email: string|null, is_verified: bool, roles: list<string>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_verified' => $this->isVerified,
            'roles' => $this->roles,
        ];
    }
}
