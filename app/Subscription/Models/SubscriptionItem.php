<?php

declare(strict_types=1);

namespace App\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

final class SubscriptionItem extends CashierSubscriptionItem
{
    use HasUlids;
}
