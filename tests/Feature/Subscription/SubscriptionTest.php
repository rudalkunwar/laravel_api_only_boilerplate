<?php

declare(strict_types=1);

use App\User\Models\User;
use Laravel\Sanctum\Sanctum;

describe('guest access', function (): void {
    it('blocks guests from reading the subscription', function (): void {
        $this->getJson('/api/v1/subscriptions/current')->assertUnauthorized();
    });

    it('blocks guests from creating a checkout', function (): void {
        $this->postJson('/api/v1/subscriptions/checkout', ['plan' => 'price_monthly'])
            ->assertUnauthorized();
    });

    it('blocks guests from accessing the billing portal', function (): void {
        $this->postJson('/api/v1/subscriptions/portal')->assertUnauthorized();
    });

    it('blocks guests from cancelling a subscription', function (): void {
        $this->postJson('/api/v1/subscriptions/cancel')->assertUnauthorized();
    });

    it('blocks guests from resuming a subscription', function (): void {
        $this->postJson('/api/v1/subscriptions/resume')->assertUnauthorized();
    });
});

describe('authenticated user', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    });

    it('returns the current subscription as null when user has none', function (): void {
        $this->getJson('/api/v1/subscriptions/current')
            ->assertOk()
            ->assertJsonPath('data', null);
    });

    it('validates the checkout request', function (): void {
        $this->postJson('/api/v1/subscriptions/checkout', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['plan']);
    });

    it('validates the plan field is required', function (): void {
        $this->postJson('/api/v1/subscriptions/checkout', [
            'plan' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['plan']);
    });

    it('validates the success_url format', function (): void {
        $this->postJson('/api/v1/subscriptions/checkout', [
            'plan' => 'price_monthly',
            'success_url' => 'not-a-url',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['success_url']);
    });

    it('validates the cancel_url format', function (): void {
        $this->postJson('/api/v1/subscriptions/checkout', [
            'plan' => 'price_monthly',
            'cancel_url' => 'not-a-url',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cancel_url']);
    });

    it('returns 404 when cancelling a non-existent subscription', function (): void {
        $this->postJson('/api/v1/subscriptions/cancel')
            ->assertNotFound()
            ->assertJsonPath('message', 'No active subscription found.');
    });

    it('returns 404 when resuming a non-existent subscription', function (): void {
        $this->postJson('/api/v1/subscriptions/resume')
            ->assertNotFound()
            ->assertJsonPath('message', 'No subscription on grace period found.');
    });
});
