<?php

declare(strict_types=1);

use App\User\Models\User;
use Laravel\Sanctum\Sanctum;

it('blocks guests from reading the profile', function (): void {
    $this->getJson('/api/v1/user')->assertUnauthorized();
});

it('returns the authenticated user profile', function (): void {
    $user = User::factory()->create(['name' => 'Linus', 'email' => 'linus@example.com']);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'is_verified', 'roles', 'permissions', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('data.email', 'linus@example.com');
});

it('updates the profile name and email', function (): void {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/user', [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new@example.com')
        ->assertJsonPath('message', 'Profile updated.');

    expect($user->refresh())
        ->name->toBe('New Name')
        ->email->toBe('new@example.com');
});

it('resets email verification when the email changes', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/user', [
        'name' => $user->name,
        'email' => 'changed@example.com',
    ])->assertOk();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('keeps verification when the email is unchanged', function (): void {
    $user = User::factory()->create(['email' => 'same@example.com']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/user', [
        'name' => 'Renamed',
        'email' => 'same@example.com',
    ])->assertOk();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('validates the profile update payload', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/v1/user', ['name' => '', 'email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

it('prevents using an email already taken by another user', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/user', [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
