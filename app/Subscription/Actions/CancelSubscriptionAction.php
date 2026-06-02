<?php

declare(strict_types=1);

namespace App\Subscription\Actions;

use App\Subscription\SubscriptionType;
use App\User\Models\User;
use Laravel\Cashier\Subscription;

final readonly class CancelSubscriptionAction
{
    public function execute(User $user, string $type = SubscriptionType::DEFAULT): ?Subscription
    {
        $subscription = $user->subscription($type);

        if (!$subscription instanceof Subscription || !$subscription->active()) {
            return null;
        }

        $subscription->cancel();

        return $subscription->fresh();
    }
}
