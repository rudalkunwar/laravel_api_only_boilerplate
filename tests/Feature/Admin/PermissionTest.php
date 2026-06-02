<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);
    Sanctum::actingAs($admin);
});

it('lists all permissions', function (): void {
    $this->getJson('/api/v1/admin/permissions')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
            ],
        ]);
});
