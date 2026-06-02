<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Enums\Permission as PermissionEnum;
use App\Auth\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'sanctum');
        }

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::findOrCreate($roleEnum->value, 'sanctum');

            $role->syncPermissions(
                array_map(static fn (PermissionEnum $permission): string => $permission->value, $roleEnum->permissions()),
            );
        }
    }
}
