<?php

declare(strict_types=1);

namespace App\User\Data;

use App\Support\Data\Input;

final readonly class UpdateProfileData
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Input::string($data, 'name'),
            email: Input::string($data, 'email'),
        );
    }
}
