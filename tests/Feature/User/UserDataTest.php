<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Data\UserData;
use App\User\Models\User;

it('builds a data object from a user model', function (): void {
    $user = User::factory()->create(['name' => 'Ada', 'email' => 'ada@example.com']);
    $user->assignRole(Role::User->value);

    $data = UserData::fromModel($user);

    expect($data->id)->toBe($user->id)
        ->and($data->name)->toBe('Ada')
        ->and($data->email)->toBe('ada@example.com')
        ->and($data->isVerified)->toBeTrue()
        ->and($data->roles)->toBe(['user']);
});

it('serialises to a snake-cased array', function (): void {
    $user = User::factory()->unverified()->create(['name' => 'Ada', 'email' => 'ada@example.com']);

    expect(UserData::fromModel($user)->toArray())->toBe([
        'id' => $user->id,
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'is_verified' => false,
        'roles' => [],
    ]);
});
