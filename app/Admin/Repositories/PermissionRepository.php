<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

final readonly class PermissionRepository
{
    /**
     * @return Collection<int, Permission>
     */
    public function all(): Collection
    {
        return Permission::query()->get();
    }
}
