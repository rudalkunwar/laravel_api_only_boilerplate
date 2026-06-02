<?php

declare(strict_types=1);

use App\User\Models\EmailOtp;
use App\User\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create(['email' => null, 'email_verified_at' => null]);
    Sanctum::actingAs($this->user);
});

it('sends otp for email setup', function (): void {
    $response = $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'new@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'OTP sent to your email.');

    $this->assertDatabaseHas('email_otps', [
        'user_id' => $this->user->id,
        'email' => 'new@example.com',
    ]);
});

it('requires unique email when sending otp', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422);
});

it('verifies otp and updates email', function (): void {
    $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'verify@example.com',
    ]);

    $otp = EmailOtp::query()
        ->where('user_id', $this->user->id)
        ->first();

    $response = $this->postJson('/api/v1/user/email/verify-otp', [
        'email' => 'verify@example.com',
        'otp' => $otp->otp,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.email', 'verify@example.com');

    expect($this->user->fresh())
        ->email->toBe('verify@example.com')
        ->email_verified_at->not->toBeNull();
});

it('rejects invalid otp', function (): void {
    $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'badotp@example.com',
    ]);

    $response = $this->postJson('/api/v1/user/email/verify-otp', [
        'email' => 'badotp@example.com',
        'otp' => '000000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});
