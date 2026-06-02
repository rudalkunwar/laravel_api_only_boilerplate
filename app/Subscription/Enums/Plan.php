<?php

declare(strict_types=1);

namespace App\Subscription\Enums;

enum Plan: string
{
    case Free = 'free';
    case Monthly = 'price_monthly';
    case Yearly = 'price_yearly';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Monthly => 'Monthly Pro',
            self::Yearly => 'Yearly Pro',
        };
    }

    public function price(): int
    {
        return match ($this) {
            self::Free => 0,
            self::Monthly => 999,
            self::Yearly => 9999,
        };
    }

    public function interval(): string
    {
        return match ($this) {
            self::Free => 'none',
            self::Monthly => 'month',
            self::Yearly => 'year',
        };
    }

    /**
     * @return list<string>
     */
    public function features(): array
    {
        return match ($this) {
            self::Free => ['Basic access', 'Community support'],
            self::Monthly => ['Everything in Free', 'Priority support', 'Advanced features', 'Unlimited projects'],
            self::Yearly => ['Everything in Monthly', '2 months free', 'Premium support', 'Early access'],
        };
    }
}
