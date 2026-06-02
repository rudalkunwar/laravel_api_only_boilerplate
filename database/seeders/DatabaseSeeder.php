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
        $this->createDemoUser('Admin User', 'admin@example.com', Role::Admin);
        $this->createDemoUser('Test User', 'test@example.com', Role::User);

        if (User::query()->count() >= 12) {
            return;
        }

        User::factory(10)
            ->create()
            ->each(static fn (User $user): mixed => $user->assignRole(Role::User->value));
    }

    /**
     * Idempotently create a fixed demo account so re-running the seeder is safe.
     */
    private function createDemoUser(string $name, string $email, Role $role): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'email_verified_at' => now(), 'password' => 'password'],
        );

        if (!$user->hasRole($role->value)) {
            $user->assignRole($role->value);
        }
    }
}
