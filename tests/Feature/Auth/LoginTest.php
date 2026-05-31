<?php

declare(strict_types=1);

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

it('authenticates a user with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'grace@example.com',
        'password' => Hash::make('Sup3r-Secret!'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'grace@example.com',
        'password' => 'Sup3r-Secret!',
        'device_name' => 'iphone',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure(['data' => ['user', 'token', 'token_type'], 'message'])
        ->assertJsonPath('data.user.email', 'grace@example.com');

    expect($user->tokens()->where('name', 'iphone')->exists())->toBeTrue();
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'grace@example.com',
        'password' => Hash::make('Sup3r-Secret!'),
    ]);

    $this->postJson('/api/v1/login', [
        'email' => 'grace@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('requires email and password to login', function (): void {
    $this->postJson('/api/v1/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('revokes the current token on logout', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    expect($user->tokens()->count())->toBe(0);
});

it('blocks logout for guests', function (): void {
    $this->postJson('/api/v1/logout')->assertUnauthorized();
});
