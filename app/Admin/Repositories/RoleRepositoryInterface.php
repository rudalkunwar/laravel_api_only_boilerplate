<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use App\Auth\Models\Role;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    /** @return Collection<int, Role> */
    public function all(): Collection;

    public function findById(string $id): ?Role;

    public function findOrCreate(string $name, string $guardName = 'sanctum'): Role;

    /** @param array<string, mixed> $attributes */
    public function update(Role $role, array $attributes): Role;

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): Role;

    public function delete(Role $role): void;
}
