<?php

declare(strict_types=1);

use App\Subscription\Enums\Plan;

it('exposes stable public plan identifiers', function (): void {
    expect(array_column(Plan::cases(), 'value'))
        ->toBe(['free', 'monthly', 'yearly']);
});
