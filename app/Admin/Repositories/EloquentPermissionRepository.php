<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use App\Auth\Models\Permission;
use Illuminate\Support\Collection;

final readonly class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    /** @return Collection<int, Permission> */
    public function all(): Collection
    {
        return Permission::query()->get();
    }
}
