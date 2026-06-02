<?php

declare(strict_types=1);

namespace App\Subscription\Actions;

use App\User\Models\User;
use Laravel\Cashier\Subscription;

final readonly class CancelSubscriptionAction
{
    public function execute(User $user, string $type = 'default'): ?Subscription
    {
        $subscription = $user->subscription($type);

        if ($subscription === null || !$subscription->active()) {
            return null;
        }

        $subscription->cancel();

        return $subscription->fresh();
    }
}
