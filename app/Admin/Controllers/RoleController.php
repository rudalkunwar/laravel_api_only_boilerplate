<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Repositories\RoleRepository;
use App\Admin\Requests\RoleStoreRequest;
use App\Admin\Requests\RoleUpdateRequest;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepository $roles,
    ) {}

    public function index(): JsonResponse
    {
        $roles = $this->roles->all()->map(fn (Role $role): array => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
        ]);

        return ApiResponse::success($roles);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = $this->roles->findOrCreate($validated['name'], 'sanctum');

        if (isset($validated['permissions'])) {
            $this->roles->syncPermissions($role, $validated['permissions']);
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

    public function update(int $id, RoleUpdateRequest $request): JsonResponse
    {
        $role = $this->roles->findById($id);

        if (!$role instanceof Role) {
            throw new HttpException(404, 'Role not found.');
        }

        $validated = $request->validated();

        if (isset($validated['name'])) {
            $this->roles->update($role, ['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $this->roles->syncPermissions($role, $validated['permissions']);
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
        $role = $this->roles->findById($id);

        if (!$role instanceof Role) {
            throw new HttpException(404, 'Role not found.');
        }

        if ($role->name === 'admin') {
            throw new HttpException(422, 'Cannot delete the admin role.');
        }

        $this->roles->delete($role);

        return ApiResponse::message('Role deleted.');
    }
}
