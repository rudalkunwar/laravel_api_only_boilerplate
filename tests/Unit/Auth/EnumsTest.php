<?php

declare(strict_types=1);

use App\Auth\Enums\Permission;
use App\Auth\Enums\Role;

it('exposes every permission value', function (): void {
    expect(Permission::values())
        ->toBe(['users.view', 'users.create', 'users.update', 'users.delete']);
});

it('exposes every role value', function (): void {
    expect(Role::values())->toBe(['admin', 'user']);
});

it('grants administrators all permissions', function (): void {
    expect(Role::Admin->permissions())->toBe(Permission::cases());
});

it('grants standard users no permissions', function (): void {
    expect(Role::User->permissions())->toBe([]);
});
