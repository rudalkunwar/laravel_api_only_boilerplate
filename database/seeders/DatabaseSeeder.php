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
        // Roles and permissions are required in every environment.
        $this->call(RolePermissionSeeder::class);

        // Demo accounts use a well-known password ("password") and must never
        // be created on a live system. Use a dedicated admin-provisioning flow
        // in production instead.
        if (app()->isProduction()) {
            return;
        }

        $this->seedDemoUsers();
    }

    private function seedDemoUsers(): void
    {
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
