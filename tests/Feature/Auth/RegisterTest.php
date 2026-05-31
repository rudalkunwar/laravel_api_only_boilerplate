<?php

declare(strict_types=1);

use App\Domain\Auth\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('registers a new user and issues a token', function (): void {
    Notification::fake();

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'Sup3r-Secret!',
        'password_confirmation' => 'Sup3r-Secret!',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'is_verified', 'roles'],
                'token',
                'token_type',
            ],
            'message',
        ])
        ->assertJsonPath('data.user.email', 'ada@example.com')
        ->assertJsonPath('data.token_type', 'Bearer');

    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

    expect($user->hasRole(Role::User->value))->toBeTrue()
        ->and(Hash::check('Sup3r-Secret!', $user->password))->toBeTrue();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('requires name, email and password', function (): void {
    $this->postJson('/api/v1/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects a duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/register', [
        'name' => 'Someone',
        'email' => 'taken@example.com',
        'password' => 'Sup3r-Secret!',
        'password_confirmation' => 'Sup3r-Secret!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects a weak or unconfirmed password', function (): void {
    $this->postJson('/api/v1/register', [
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'password' => 'weak',
        'password_confirmation' => 'nope',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});
