<?php

declare(strict_types=1);

use App\Domain\Auth\Data\LoginData;
use App\Domain\Auth\Data\RegisterData;
use App\Domain\Auth\Data\ResetPasswordData;
use App\Domain\User\Data\UpdateProfileData;

it('builds register data from an array', function (): void {
    $data = RegisterData::fromArray([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'secret',
    ]);

    expect($data->name)->toBe('Ada')
        ->and($data->email)->toBe('ada@example.com')
        ->and($data->password)->toBe('secret');
});

it('defaults the login device name to api', function (): void {
    $data = LoginData::fromArray([
        'email' => 'ada@example.com',
        'password' => 'secret',
    ]);

    expect($data->deviceName)->toBe('api');
});

it('keeps a provided login device name', function (): void {
    $data = LoginData::fromArray([
        'email' => 'ada@example.com',
        'password' => 'secret',
        'device_name' => 'pixel',
    ]);

    expect($data->deviceName)->toBe('pixel');
});

it('builds reset password data from an array', function (): void {
    $data = ResetPasswordData::fromArray([
        'email' => 'ada@example.com',
        'token' => 'tok',
        'password' => 'secret',
    ]);

    expect($data->email)->toBe('ada@example.com')
        ->and($data->token)->toBe('tok')
        ->and($data->password)->toBe('secret');
});

it('builds update profile data from an array', function (): void {
    $data = UpdateProfileData::fromArray([
        'name' => 'Ada',
        'email' => 'ada@example.com',
    ]);

    expect($data->name)->toBe('Ada')
        ->and($data->email)->toBe('ada@example.com');
});
