<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

final readonly class EloquentRoleRepository implements RoleRepositoryInterface
{
    /** @return Collection<int, Role> */
    public function all(): Collection
    {
        return Role::query()->with('permissions')->get();
    }

    public function findById(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function findOrCreate(string $name, string $guardName = 'sanctum'): Role
    {
        return Role::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => $guardName,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Role $role, array $attributes): Role
    {
        $role->update($attributes);

        return $role->refresh();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        $role->load('permissions');

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
