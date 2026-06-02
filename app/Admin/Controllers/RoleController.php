<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Repositories\RoleRepositoryInterface;
use App\Admin\Requests\RoleStoreRequest;
use App\Admin\Requests\RoleUpdateRequest;
use App\Admin\Resources\RoleResource;
use App\Auth\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Support\Data\Input;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(RoleResource::collection($this->roles->all()));
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = $this->roles->findOrCreate(Input::string($validated, 'name'), 'sanctum');

        if (array_key_exists('permissions', $validated)) {
            $this->roles->syncPermissions($role, Input::stringList($validated, 'permissions'));
        }

        $role->load('permissions');

        return ApiResponse::success(RoleResource::make($role), 'Role created.', 201);
    }

    public function update(int $id, RoleUpdateRequest $request): JsonResponse
    {
        $role = $this->roles->findById($id);

        throw_unless($role instanceof Role, HttpException::class, 404, 'Role not found.');

        $validated = $request->validated();

        if (array_key_exists('name', $validated)) {
            $this->roles->update($role, ['name' => Input::string($validated, 'name')]);
        }

        if (array_key_exists('permissions', $validated)) {
            $this->roles->syncPermissions($role, Input::stringList($validated, 'permissions'));
        }

        $role->load('permissions');

        return ApiResponse::success(RoleResource::make($role), 'Role updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = $this->roles->findById($id);

        throw_unless($role instanceof Role, HttpException::class, 404, 'Role not found.');

        throw_if($role->name === RoleEnum::Admin->value, HttpException::class, 422, 'Cannot delete the admin role.');

        $this->roles->delete($role);

        return ApiResponse::message('Role deleted.');
    }
}
