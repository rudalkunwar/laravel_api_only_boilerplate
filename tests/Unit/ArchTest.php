<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
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
    ->classes()
    ->toBeFinal();

arch('controllers extend the base controller')
    ->expect('App\Auth\Controllers')
    ->and('App\User\Controllers')
    ->toExtend(Controller::class);

arch('controllers do not depend on eloquent models directly for queries')
    ->expect('App\Auth\Controllers')
    ->and('App\User\Controllers')
    ->not->toUse(DB::class);

arch('data transfer objects are immutable')
    ->expect('App\Auth\Data')
    ->toBeReadonly();

arch('value objects in the user domain are immutable')
    ->expect('App\User\Data')
    ->toBeReadonly();

arch('enums live in enum namespaces')
    ->expect('App\Auth\Enums')
    ->toBeEnums();
