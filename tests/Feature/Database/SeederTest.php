<?php

declare(strict_types=1);

use App\User\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Role;

it('seeds demo accounts outside production', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'admin@example.com')->exists())->toBeTrue()
        ->and(Role::query()->where('name', 'admin')->exists())->toBeTrue();
});

it('never seeds demo accounts in production', function (): void {
    app()['env'] = 'production';

    // Mirrors a real deploy: `php artisan db:seed --force`.
    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    // Roles/permissions are still seeded, but no well-known-password users exist.
    expect(User::query()->count())->toBe(0)
        ->and(Role::query()->where('name', 'admin')->exists())->toBeTrue();
});
