<?php

declare(strict_types=1);

namespace App\User\Repositories;

use App\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->refresh();
    }

    public function resetEmailVerification(User $user): User
    {
        $user->forceFill(['email_verified_at' => null])->save();

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = User::query()->latest()->paginate($perPage);

        return $paginator;
    }

    public function paginateWithCriteria(array $criteria = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        if ($search = $criteria['search'] ?? null) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $criteria['role'] ?? null) {
            $query->role($role);
        }

        $sort = $criteria['sort'] ?? 'created_at';
        $direction = $criteria['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = $query->paginate($perPage);

        return $paginator;
    }

    public function assignRole(User $user, string|array $roles): User
    {
        $user->assignRole($roles);

        return $user->refresh();
    }

    public function syncRoles(User $user, string|array $roles): User
    {
        $user->syncRoles($roles);

        return $user->refresh();
    }

    public function resetPassword(User $user, string $hashedPassword): User
    {
        $user->forceFill([
            'password' => $hashedPassword,
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        return $user->refresh();
    }
}
