<?php

declare(strict_types=1);

namespace App\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Cashier\Subscription as CashierSubscription;

final class Subscription extends CashierSubscription
{
    use HasUlids;
}
