<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use App\Auth\Models\Permission;
use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    /** @return Collection<int, Permission> */
    public function all(): Collection;
}
