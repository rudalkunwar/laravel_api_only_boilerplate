<?php

declare(strict_types=1);

use App\User\Models\EmailOtp;
use App\User\Models\User;
use App\User\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Notification::fake();
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

it('emails the otp code to the target address', function (): void {
    $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'new@example.com',
    ])->assertOk();

    $emailOtp = EmailOtp::query()->where('user_id', $this->user->id)->firstOrFail();

    Notification::assertSentOnDemand(
        EmailOtpNotification::class,
        fn (EmailOtpNotification $notification, array $channels, object $notifiable): bool => Hash::check($notification->otp, $emailOtp->otp_hash)
            && $notification->otp !== $emailOtp->otp_hash
            && $notifiable->routes['mail'] === 'new@example.com',
    );
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

    $otp = '';
    Notification::assertSentOnDemand(
        EmailOtpNotification::class,
        function (EmailOtpNotification $notification) use (&$otp): bool {
            $otp = $notification->otp;

            return true;
        },
    );

    $response = $this->postJson('/api/v1/user/email/verify-otp', [
        'email' => 'verify@example.com',
        'otp' => $otp,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.email', 'verify@example.com');

    expect($this->user->fresh())
        ->email->toBe('verify@example.com')
        ->email_verified_at->not->toBeNull();
});

it('rate limits otp attempts', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/user/email/send-otp', [
            'email' => "attempt{$attempt}@example.com",
        ])->assertOk();
    }

    $this->postJson('/api/v1/user/email/send-otp', [
        'email' => 'blocked@example.com',
    ])->assertTooManyRequests();
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
