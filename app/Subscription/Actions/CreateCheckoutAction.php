<?php

declare(strict_types=1);

namespace App\Subscription\Actions;

use App\Subscription\Data\CheckoutData;
use App\Subscription\Enums\Plan;
use App\Subscription\SubscriptionType;
use App\User\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Cashier\Checkout;

final readonly class CreateCheckoutAction
{
    public function execute(User $user, CheckoutData $data): Checkout
    {
        $subscription = $user->newSubscription(
            SubscriptionType::DEFAULT,
            Plan::from($data->plan)->stripePriceId(),
        );

        if ($data->allowPromotionCodes) {
            $subscription->allowPromotionCodes();
        }

        if ($data->coupon !== null) {
            $subscription->withCoupon($data->coupon);
        }

        $frontendUrl = rtrim(Config::string('app.frontend_url'), '/');

        return $subscription->checkout([
            'success_url' => $data->successUrl ?? $frontendUrl.'/subscription/success',
            'cancel_url' => $data->cancelUrl ?? $frontendUrl.'/subscription/cancel',
        ]);
    }
}
