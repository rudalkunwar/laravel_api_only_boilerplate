<?php

declare(strict_types=1);

use App\Subscription\Enums\Plan;
use Illuminate\Support\Facades\Config;

it('lists all subscription plans', function (): void {
    $response = $this->getJson('/api/v1/subscriptions/plans');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'interval', 'features'],
            ],
        ])
        ->assertJsonPath('data.0.id', 'free')
        ->assertJsonPath('data.1.id', 'monthly')
        ->assertJsonPath('data.2.id', 'yearly');
});

it('resolves configured Stripe price IDs', function (): void {
    Config::set('services.stripe.prices.monthly', 'price_monthly_test');
    Config::set('services.stripe.prices.yearly', 'price_yearly_test');

    expect(Plan::Monthly->stripePriceId())->toBe('price_monthly_test')
        ->and(Plan::Yearly->stripePriceId())->toBe('price_yearly_test');
});

it('fails clearly when a Stripe price is not configured', function (): void {
    Config::set('services.stripe.prices.monthly', '');

    expect(fn (): string => Plan::Monthly->stripePriceId())
        ->toThrow(RuntimeException::class, 'The Stripe price for the monthly plan is not configured.');
});
