<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()->with('permissions')->get()->map(fn (Role $role): array => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ]);

        return ApiResponse::success($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::findOrCreate($validated['name'], 'sanctum');

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return ApiResponse::success([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ], 'Role created.', 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $role = Role::query()->find($id);

        if (!$role instanceof Role) {
            throw new HttpException(404, 'Role not found.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,'.$id],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return ApiResponse::success([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ], 'Role updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::query()->find($id);

        if (!$role instanceof Role) {
            throw new HttpException(404, 'Role not found.');
        }

        if ($role->name === 'admin') {
            throw new HttpException(422, 'Cannot delete the admin role.');
        }

        $role->delete();

        return ApiResponse::message('Role deleted.');
    }
}
