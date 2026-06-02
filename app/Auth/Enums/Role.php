<?php

declare(strict_types=1);

namespace App\Auth\Enums;

enum Role: string
{
    case Admin = 'admin';
    case User = 'user';

    /**
     * The permissions granted to the role.
     *
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::User => [],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}
