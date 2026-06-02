<?php

declare(strict_types=1);

namespace App\Subscription\Data;

final readonly class CheckoutData
{
    public function __construct(
        public string $plan,
        public ?string $coupon = null,
        public bool $allowPromotionCodes = true,
        public ?string $successUrl = null,
        public ?string $cancelUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plan: (string) ($data['plan'] ?? ''),
            coupon: isset($data['coupon']) ? (string) $data['coupon'] : null,
            allowPromotionCodes: isset($data['allow_promotion_codes']) ? (bool) $data['allow_promotion_codes'] : true,
            successUrl: isset($data['success_url']) ? (string) $data['success_url'] : null,
            cancelUrl: isset($data['cancel_url']) ? (string) $data['cancel_url'] : null,
        );
    }
}
