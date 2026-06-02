<?php

declare(strict_types=1);

namespace App\Subscription\Actions;

use App\Subscription\Data\CheckoutData;
use App\User\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Cashier\Checkout;

final readonly class CreateCheckoutAction
{
    public function execute(User $user, CheckoutData $data): Checkout
    {
        $subscription = $user->newSubscription('default', $data->plan);

        if ($data->allowPromotionCodes) {
            $subscription->allowPromotionCodes();
        }

        if ($data->coupon !== null) {
            $subscription->withCoupon($data->coupon);
        }

        $appUrl = Config::string('app.url');

        return $subscription->checkout([
            'success_url' => $data->successUrl ?? $appUrl.'/subscription/success',
            'cancel_url' => $data->cancelUrl ?? $appUrl.'/subscription/cancel',
        ]);
    }
}
