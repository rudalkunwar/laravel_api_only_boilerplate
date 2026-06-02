<?php

declare(strict_types=1);

arch('the whole application uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debugging helpers are left behind')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die', 'var_export'])
    ->not->toBeUsed();

arch('actions are final and invoked through an execute method')
    ->expect('App\Domain')
    ->classes()
    ->toBeFinal();

arch('controllers extend the base controller')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('controllers do not depend on eloquent models directly for queries')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

arch('data transfer objects are immutable')
    ->expect('App\Domain\Auth\Data')
    ->toBeReadonly();

arch('value objects in the user domain are immutable')
    ->expect('App\Domain\User\Data')
    ->toBeReadonly();

arch('enums live in enum namespaces')
    ->expect('App\Domain\Auth\Enums')
    ->toBeEnums();
