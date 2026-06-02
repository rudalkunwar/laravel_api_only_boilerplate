<?php

declare(strict_types=1);

use App\User\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

function verificationUrl(User $user): string
{
    return URL::temporarySignedRoute(
        'api.v1.verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
    );
}

it('verifies an email from a valid signed url', function (): void {
    Event::fake();

    $user = User::factory()->unverified()->create();

    $this->getJson(verificationUrl($user))
        ->assertOk()
        ->assertJsonPath('message', 'Email address verified successfully.');

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('rejects a tampered verification hash', function (): void {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong@example.com')],
    );

    $this->getJson($url)->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects an unsigned verification url', function (): void {
    $user = User::factory()->unverified()->create();

    $this->getJson("/api/v1/email/verify/{$user->id}/".sha1($user->getEmailForVerification()))
        ->assertForbidden();
});

it('reports when the email is already verified', function (): void {
    $user = User::factory()->create();

    $this->getJson(verificationUrl($user))
        ->assertOk()
        ->assertJsonPath('message', 'Email address already verified.');
});

it('resends the verification notification to an unverified user', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/email/verification-notification')
        ->assertOk()
        ->assertJsonPath('message', 'Verification link sent.');

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not resend when already verified', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/email/verification-notification')
        ->assertOk()
        ->assertJsonPath('message', 'Email address already verified.');

    Notification::assertNothingSent();
});
