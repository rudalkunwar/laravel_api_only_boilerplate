<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);
    Sanctum::actingAs($admin);
});

it('lists all roles', function (): void {
    $this->getJson('/api/v1/admin/roles')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['id', 'name', 'guard_name', 'permissions', 'created_at', 'updated_at'],
            ],
        ]);
});

it('creates a role with permissions', function (): void {
    $perm = Permission::first();

    $this->postJson('/api/v1/admin/roles', [
        'name' => 'editor',
        'permissions' => [$perm->name],
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'editor')
        ->assertJsonPath('data.guard_name', 'sanctum')
        ->assertJsonPath('data.permissions', [$perm->name]);
});

it('updates a role', function (): void {
    $role = Spatie\Permission\Models\Role::findOrCreate('editor', 'sanctum');

    $this->putJson('/api/v1/admin/roles/'.$role->id, [
        'name' => 'super-editor',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'super-editor');
});

it('prevents deleting the admin role', function (): void {
    $role = Spatie\Permission\Models\Role::where('name', Role::Admin->value)->first();

    $this->deleteJson('/api/v1/admin/roles/'.$role->id)
        ->assertStatus(422)
        ->assertJsonPath('message', 'Cannot delete the admin role.');
});

it('deletes a non-admin role', function (): void {
    $role = Spatie\Permission\Models\Role::findOrCreate('temporary', 'sanctum');

    $this->deleteJson('/api/v1/admin/roles/'.$role->id)
        ->assertOk()
        ->assertJsonPath('message', 'Role deleted.');
});
