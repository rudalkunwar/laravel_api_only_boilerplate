<?php

declare(strict_types=1);

use App\Support\Data\Input;

it('reads strings with a fallback default', function (): void {
    expect(Input::string(['name' => 'Ada'], 'name'))->toBe('Ada')
        ->and(Input::string(['count' => 5], 'count'))->toBe('5')
        ->and(Input::string([], 'missing'))->toBe('')
        ->and(Input::string([], 'missing', 'fallback'))->toBe('fallback')
        ->and(Input::string(['bag' => ['nested']], 'bag'))->toBe('');
});

it('reads nullable strings treating empty and non-scalar as null', function (): void {
    expect(Input::nullableString(['email' => 'ada@example.com'], 'email'))->toBe('ada@example.com')
        ->and(Input::nullableString(['email' => ''], 'email'))->toBeNull()
        ->and(Input::nullableString([], 'email'))->toBeNull()
        ->and(Input::nullableString(['email' => ['x']], 'email'))->toBeNull();
});

it('reads integers from numeric values', function (): void {
    expect(Input::integer(['n' => 7], 'n'))->toBe(7)
        ->and(Input::integer(['n' => '15'], 'n'))->toBe(15)
        ->and(Input::integer([], 'n', 15))->toBe(15)
        ->and(Input::integer(['n' => 'abc'], 'n', 3))->toBe(3);
});

it('reads booleans with sensible coercion', function (): void {
    expect(Input::boolean(['flag' => true], 'flag'))->toBeTrue()
        ->and(Input::boolean(['flag' => '1'], 'flag'))->toBeTrue()
        ->and(Input::boolean(['flag' => '0'], 'flag'))->toBeFalse()
        ->and(Input::boolean([], 'flag', true))->toBeTrue();
});

it('reads a list of strings discarding non-strings', function (): void {
    expect(Input::stringList(['roles' => ['admin', 'user']], 'roles'))->toBe(['admin', 'user'])
        ->and(Input::stringList(['roles' => ['admin', 1, null, 'user']], 'roles'))->toBe(['admin', 'user'])
        ->and(Input::stringList([], 'roles'))->toBe([])
        ->and(Input::stringList(['roles' => 'admin'], 'roles'))->toBe([]);
});
