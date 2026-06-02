<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Enums\Role;
use App\User\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()
            ->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ])
            ->assignRole(Role::Admin->value);

        User::factory()
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])
            ->assignRole(Role::User->value);

        User::factory(10)
            ->create()
            ->each(static fn (User $user): mixed => $user->assignRole(Role::User->value));
    }
}
