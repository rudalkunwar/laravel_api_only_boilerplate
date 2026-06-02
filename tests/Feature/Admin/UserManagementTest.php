<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::Admin);

    $this->regular = User::factory()->create();
    $this->regular->assignRole(Role::User);
});

it('returns 403 when non-admin accesses user index', function (): void {
    Sanctum::actingAs($this->regular);

    $this->getJson('/api/v1/admin/users')->assertForbidden();
});

it('lists paginated users for admin', function (): void {
    Sanctum::actingAs($this->admin);

    User::factory(5)->create()->each(fn (User $u) => $u->assignRole(Role::User));

    $this->getJson('/api/v1/admin/users')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['data', 'meta', 'links'],
        ]);
});

it('shows a specific user for admin', function (): void {
    Sanctum::actingAs($this->admin);

    $user = User::factory()->create(['name' => 'Visible User']);
    $user->assignRole(Role::User);

    $this->getJson('/api/v1/admin/users/'.$user->id)
        ->assertOk()
        ->assertJsonPath('data.name', 'Visible User');
});

it('returns 404 when showing non-existent user', function (): void {
    Sanctum::actingAs($this->admin);

    $this->getJson('/api/v1/admin/users/99999')->assertNotFound();
});

it('creates a new user via admin', function (): void {
    Sanctum::actingAs($this->admin);

    $payload = [
        'name' => 'New Admin User',
        'email' => 'newadmin@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => Role::Admin->value,
    ];

    $this->postJson('/api/v1/admin/users', $payload)
        ->assertCreated()
        ->assertJsonPath('data.name', 'New Admin User')
        ->assertJsonPath('data.email', 'newadmin@example.com')
        ->assertJsonPath('data.roles', [Role::Admin->value]);

    $this->assertDatabaseHas('users', ['email' => 'newadmin@example.com']);
});

it('updates a user via admin', function (): void {
    Sanctum::actingAs($this->admin);

    $user = User::factory()->create(['name' => 'Old Name']);
    $user->assignRole(Role::User);

    $this->putJson('/api/v1/admin/users/'.$user->id, [
        'name' => 'Updated Name',
        'role' => Role::Admin->value,
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.roles', [Role::Admin->value]);
});

it('deletes a user via admin (not self)', function (): void {
    Sanctum::actingAs($this->admin);

    $target = User::factory()->create();
    $target->assignRole(Role::User);

    $this->deleteJson('/api/v1/admin/users/'.$target->id)
        ->assertOk()
        ->assertJsonPath('message', 'User deleted.');

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

it('prevents admin from deleting their own account', function (): void {
    Sanctum::actingAs($this->admin);

    $this->deleteJson('/api/v1/admin/users/'.$this->admin->id)
        ->assertStatus(422)
        ->assertJsonPath('message', 'Cannot delete your own account.');
});
