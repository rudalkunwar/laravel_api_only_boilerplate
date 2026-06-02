<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use App\Subscription\Controllers\WebhookController;
use Illuminate\Support\Facades\DB;

arch('the whole application uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debugging helpers are left behind')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die', 'var_export'])
    ->not->toBeUsed();

arch('actions are final and invoked through an execute method')
    ->expect('App\Auth\Actions')
    ->and('App\User\Actions')
    ->and('App\OAuth\Actions')
    ->and('App\Subscription\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('execute');

arch('controllers extend the base controller')
    ->expect('App\Auth\Controllers')
    ->and('App\User\Controllers')
    ->and('App\Admin\Controllers')
    ->and('App\OAuth\Controllers')
    ->and('App\Subscription\Controllers')
    ->toExtend(Controller::class)
    ->ignoring(WebhookController::class);

arch('controllers do not query the database directly')
    ->expect('App\Auth\Controllers')
    ->and('App\User\Controllers')
    ->and('App\Admin\Controllers')
    ->and('App\OAuth\Controllers')
    ->and('App\Subscription\Controllers')
    ->not->toUse(DB::class);

arch('data transfer objects are immutable')
    ->expect('App\Auth\Data')
    ->and('App\User\Data')
    ->and('App\OAuth\Data')
    ->and('App\Subscription\Data')
    ->toBeReadonly();

arch('enums live in enum namespaces')
    ->expect('App\Auth\Enums')
    ->and('App\Subscription\Enums')
    ->toBeEnums();
