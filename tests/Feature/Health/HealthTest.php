<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns 401 for unauthenticated request', function (): void {
    $this->getJson('/api/v1/admin/health')->assertUnauthorized();
});

it('returns 403 for non-admin user', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::User);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/health')->assertForbidden();
});

it('returns health check data for admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/health');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['checks' => ['database' => ['healthy', 'message'], 'cache' => ['healthy', 'message']]],
            'message',
        ]);
});
