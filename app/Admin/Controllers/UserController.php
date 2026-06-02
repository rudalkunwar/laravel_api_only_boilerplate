<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Requests\UserIndexRequest;
use App\Admin\Requests\UserStoreRequest;
use App\Admin\Requests\UserUpdateRequest;
use App\Admin\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Data\Input;
use App\Support\Http\ApiResponse;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function index(UserIndexRequest $request): JsonResponse
    {
        $criteria = $request->validated();
        $perPage = Input::integer($criteria, 'per_page', 15);

        $users = $this->users->paginateWithCriteria($criteria, $perPage);

        return ApiResponse::success(UserResource::collection($users));
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->users->findById($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        return ApiResponse::success(UserResource::make($user));
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->users->create([
            'name' => Input::string($validated, 'name'),
            'email' => Input::string($validated, 'email'),
            'password' => Input::string($validated, 'password'),
        ]);

        $role = Input::nullableString($validated, 'role');

        if ($role !== null) {
            $this->users->assignRole($user, $role);
        }

        return ApiResponse::success(UserResource::make($user), 'User created.', 201);
    }

    public function update(int $id, UserUpdateRequest $request): JsonResponse
    {
        $user = $this->users->findById($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        $data = $request->validated();

        $emailChanged = isset($data['email']) && $data['email'] !== $user->getOriginal('email');

        $fillable = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
        ], fn ($value): bool => $value !== null);

        if ($fillable !== []) {
            $this->users->update($user, $fillable);

            if ($emailChanged) {
                $this->users->resetEmailVerification($user);
            }
        }

        $role = Input::nullableString($data, 'role');

        if ($role !== null) {
            $this->users->syncRoles($user, $role);
        }

        return ApiResponse::success(UserResource::make($user->refresh()), 'User updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        if ((int) $id === (int) auth()->id()) {
            throw new HttpException(422, 'Cannot delete your own account.');
        }

        $user = $this->users->findById($id);

        if (!$user instanceof User) {
            throw new HttpException(404, 'User not found.');
        }

        $this->users->delete($user);

        return ApiResponse::message('User deleted.');
    }
}
