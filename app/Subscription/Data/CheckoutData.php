<?php

declare(strict_types=1);

namespace App\Subscription\Data;

use App\Support\Data\Input;

final readonly class CheckoutData
{
    public function __construct(
        public string $plan,
        public ?string $coupon = null,
        public bool $allowPromotionCodes = true,
        public ?string $successUrl = null,
        public ?string $cancelUrl = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            plan: Input::string($data, 'plan'),
            coupon: Input::nullableString($data, 'coupon'),
            allowPromotionCodes: Input::boolean($data, 'allow_promotion_codes', true),
            successUrl: Input::nullableString($data, 'success_url'),
            cancelUrl: Input::nullableString($data, 'cancel_url'),
        );
    }
}
