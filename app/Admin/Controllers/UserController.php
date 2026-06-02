<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Requests\UserIndexRequest;
use App\Admin\Requests\UserStoreRequest;
use App\Admin\Requests\UserUpdateRequest;
use App\Admin\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class UserController extends Controller
{
    public function index(UserIndexRequest $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->validated('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->validated('role')) {
            $query->role($role);
        }

        $sort = $request->validated('sort', 'created_at');
        $direction = $request->validated('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = (int) $request->validated('per_page', 15);
        $users = $query->paginate($perPage);

        return ApiResponse::success(UserResource::collection($users));
    }

    public function show(int $id): JsonResponse
    {
        $user = User::query()->find($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        return ApiResponse::success(UserResource::make($user));
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        if ($role = $request->validated('role')) {
            $user->assignRole($role);
        }

        return ApiResponse::success(UserResource::make($user), 'User created.', 201);
    }

    public function update(int $id, UserUpdateRequest $request): JsonResponse
    {
        $user = User::query()->find($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        $data = $request->validated();

        $fillable = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
        ], fn ($value): bool => $value !== null);

        if ($fillable !== []) {
            $user->update($fillable);

            if (isset($data['email']) && $data['email'] !== $user->getOriginal('email')) {
                $user->forceFill(['email_verified_at' => null])->save();
            }
        }

        if (isset($data['role'])) {
            $user->syncRoles($data['role']);
        }

        return ApiResponse::success(UserResource::make($user->refresh()), 'User updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        if ((int) $id === (int) auth()->id()) {
            throw new HttpException(422, 'Cannot delete your own account.');
        }

        $user = User::query()->find($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        $user->delete();

        return ApiResponse::message('User deleted.');
    }
}
