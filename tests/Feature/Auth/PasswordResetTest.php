<?php

declare(strict_types=1);

use App\Domain\User\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('sends a password reset link', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->postJson('/api/v1/forgot-password', ['email' => 'reset@example.com'])
        ->assertOk()
        ->assertJsonPath('message', 'Password reset link sent.');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('fails to send a reset link for an unknown email', function (): void {
    $this->postJson('/api/v1/forgot-password', ['email' => 'ghost@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('resets the password with a valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->postJson('/api/v1/forgot-password', ['email' => 'reset@example.com']);

    $token = '';
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    $this->postJson('/api/v1/reset-password', [
        'token' => $token,
        'email' => 'reset@example.com',
        'password' => 'Brand-New-Pass1!',
        'password_confirmation' => 'Brand-New-Pass1!',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Password has been reset.');

    expect(Hash::check('Brand-New-Pass1!', $user->refresh()->password))->toBeTrue();
});

it('rejects an invalid reset token', function (): void {
    User::factory()->create(['email' => 'reset@example.com']);

    $this->postJson('/api/v1/reset-password', [
        'token' => 'invalid-token',
        'email' => 'reset@example.com',
        'password' => 'Brand-New-Pass1!',
        'password_confirmation' => 'Brand-New-Pass1!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
