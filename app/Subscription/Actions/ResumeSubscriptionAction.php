<?php

declare(strict_types=1);

namespace App\Subscription\Actions;

use App\Subscription\SubscriptionType;
use App\User\Models\User;
use Laravel\Cashier\Subscription;

final readonly class ResumeSubscriptionAction
{
    public function execute(User $user, string $type = SubscriptionType::DEFAULT): ?Subscription
    {
        $subscription = $user->subscription($type);

        if (!$subscription instanceof Subscription || !$subscription->onGracePeriod()) {
            return null;
        }

        $subscription->resume();

        return $subscription->fresh();
    }
}
