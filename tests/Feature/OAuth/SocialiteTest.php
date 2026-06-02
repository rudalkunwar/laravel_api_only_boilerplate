<?php

declare(strict_types=1);

use App\User\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

it('returns redirect url for google', function (): void {
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('redirect->getTargetUrl')
        ->andReturn('https://accounts.google.com/o/oauth2/auth?client_id=test');

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/redirect');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['redirect_url']]);
});

it('returns 422 for unsupported provider', function (): void {
    $response = $this->getJson('/api/v1/auth/facebook/redirect');

    $response->assertStatus(422);
});

it('can authenticate via google callback', function (): void {
    $socialiteUser = Mockery::mock(Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('12345');
    $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
    $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/callback');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'is_verified', 'roles'],
                'token',
                'token_type',
            ],
        ])
        ->assertJsonPath('data.user.email', 'john@example.com')
        ->assertJsonPath('data.token_type', 'Bearer');

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'google',
        'provider_id' => '12345',
    ]);

    expect(User::query()->where('email', 'john@example.com')->first())
        ->email_verified_at->not->toBeNull();
});

it('links existing user to new social account on callback', function (): void {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
    ]);

    $socialiteUser = Mockery::mock(Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('67890');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/callback');

    $response->assertOk();

    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '67890',
    ]);
});

it('returns existing user on repeat social login', function (): void {
    $user = User::factory()->create([
        'email' => 'repeat@example.com',
        'email_verified_at' => now(),
    ]);

    $user->socialAccounts()->create([
        'provider' => 'google',
        'provider_id' => '99999',
        'avatar_url' => 'https://example.com/avatar.jpg',
    ]);

    $socialiteUser = Mockery::mock(Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('99999');
    $socialiteUser->shouldReceive('getName')->andReturn('Repeat User');
    $socialiteUser->shouldReceive('getEmail')->andReturn('repeat@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/callback');

    $response->assertOk();

    expect($user->socialAccounts()->count())->toBe(1);
});

it('accepts callback when provider does not return email', function (): void {
    $socialiteUser = Mockery::mock(Laravel\Socialite\Two\User::class);
    $socialiteUser->shouldReceive('getId')->andReturn('noemail123');
    $socialiteUser->shouldReceive('getName')->andReturn('No Email User');
    $socialiteUser->shouldReceive('getEmail')->andReturn(null);
    $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/google/callback');

    $response->assertOk()
        ->assertJsonPath('data.user.email', null);

    $this->assertDatabaseHas('users', [
        'name' => 'No Email User',
    ]);

    expect(User::query()->where('name', 'No Email User')->first())
        ->email->toBeNull();
});
