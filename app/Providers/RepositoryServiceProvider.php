<?php

declare(strict_types=1);

namespace App\Providers;

use App\Admin\Repositories\EloquentPermissionRepository;
use App\Admin\Repositories\EloquentRoleRepository;
use App\Admin\Repositories\PermissionRepositoryInterface;
use App\Admin\Repositories\RoleRepositoryInterface;
use App\OAuth\Repositories\EloquentSocialAccountRepository;
use App\OAuth\Repositories\SocialAccountRepositoryInterface;
use App\User\Repositories\EloquentUserRepository;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        PermissionRepositoryInterface::class => EloquentPermissionRepository::class,
        RoleRepositoryInterface::class => EloquentRoleRepository::class,
        SocialAccountRepositoryInterface::class => EloquentSocialAccountRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
    ];
}
